<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cargo;
use App\Models\Curso;
use App\Models\Funcionario;
use App\Models\Movement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockNotification;
use App\Models\Supplier;
use App\Models\TeacherRequest;
use App\Models\Turma;
use App\Services\StockService;
use App\Services\TeacherRequestService;
use App\Support\EmployeeRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SenaiStockController extends Controller
{
    private const VALID_VIEWS = [
        'insights',
        'overview',
        'teacher_requests',
        'purchases',
        'history',
        'dashboard',
        'library',
        'book_registration',
        'receive',
        'withdraw',
        'movements',
        'alerts',
        'notifications',
        'suppliers',
        'classes',
        'courses',
        'people',
        'settings',
        'stock',
        'reports',
    ];

    public function index(Request $request, string $view = 'insights'): View|RedirectResponse
    {
        if (! in_array($view, self::VALID_VIEWS, true)) {
            abort(404);
        }

        $legacyViews = [
            'overview' => ['view' => 'reports'],
            'dashboard' => ['view' => 'reports'],
            'receive' => ['view' => 'stock', 'tab' => 'entrada'],
            'withdraw' => ['view' => 'stock', 'tab' => 'saida'],
            'movements' => ['view' => 'stock', 'tab' => 'historico'],
            'history' => ['view' => 'purchases', 'tab' => 'historico'],
            'alerts' => ['view' => 'insights'],
            'suppliers' => ['view' => 'purchases'],
        ];

        if (isset($legacyViews[$view])) {
            return redirect()->route('senai.dashboard', $legacyViews[$view]);
        }

        $employee = $request->session()->get('employee', []);
        $roleKey = $employee['role_key'] ?? $this->roleKey($employee['cargo'] ?? null);

        if (! $this->canAccessView($view, $roleKey)) {
            abort(403);
        }

        $books = Book::query()
            ->orderBy('subject')
            ->orderBy('title')
            ->get()
            ->map(fn (Book $book) => $this->presentBook($book))
            ->values();

        if ($books->isEmpty()) {
            $books = collect(config('senaistock.books', []))
                ->map(fn (array $book) => $book + [
                    'minimumStock' => (int) config('senaistock.low_stock_threshold', 8),
                    'location' => 'Estoque central',
                    'imageUrl' => null,
                    'status' => 'ativo',
                ]);
        }

        $processedTeacherRequests = collect($request->session()->get('processed_teacher_requests', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        $teacherRequests = $this->teacherRequestsFor($request, $books, $processedTeacherRequests);
        $professorNotifications = $roleKey === 'professor'
            ? TeacherRequest::query()
                ->where('requested_by_funcionario_id', $request->session()->get('employee.id'))
                ->with(['messages' => fn ($query) => $query->latest()])
                ->latest()
                ->get()
                ->filter(function (TeacherRequest $teacherRequest) {
                    $latestMessage = $teacherRequest->messages->first();

                    if (! $latestMessage) {
                        return false;
                    }

                    if ($teacherRequest->notifications_dismissed_message_id) {
                        return $latestMessage->id > $teacherRequest->notifications_dismissed_message_id;
                    }

                    return ! $teacherRequest->notifications_dismissed_at
                        || $latestMessage->created_at->gt($teacherRequest->notifications_dismissed_at);
                })
                ->map(function (TeacherRequest $teacherRequest) {
                    $message = $teacherRequest->messages->first();

                    return [
                        'requestId' => $teacherRequest->id,
                        'protocol' => $teacherRequest->protocol,
                        'title' => $teacherRequest->title,
                        'className' => $teacherRequest->class_name,
                        'courseName' => $teacherRequest->course_name,
                        'status' => $message?->status ?? $teacherRequest->status,
                        'message' => ($message?->status ?? $teacherRequest->status) === 'rejeitado'
                            ? 'Seu pedido foi rejeitado. Consulte o motivo na tabela Meus Pedidos.'
                            : ($message?->message ?? $teacherRequest->notes ?? 'Pedido registrado.'),
                        'date' => ($message?->created_at ?? $teacherRequest->updated_at)?->format('d/m/Y H:i'),
                    ];
                })
                ->sortByDesc('date')
                ->take(8)
                ->values()
            : collect();
        $purchaseCart = collect();
        $purchaseOrders = $this->purchaseOrdersFor($request);
        $suppliers = $this->suppliersFor();
        $notificationQuery = StockNotification::with(['teacherRequest', 'book']);
        if ($roleKey === 'professor') {
            $notificationQuery->whereHas(
                'teacherRequest',
                fn ($query) => $query->where('requested_by_funcionario_id', $request->session()->get('employee.id'))
            );
        }
        $notifications = $notificationQuery->latest()->limit(40)->get();
        $movements = Movement::with(['book', 'funcionario'])
            ->latest()
            ->limit(60)
            ->get();

        $role = EmployeeRole::fromSession($request);
        $navigationItems = EmployeeRole::navigationFor($role);
        $employeeRole = $role;
        $permissions = EmployeeRole::permissions($role);
        $employee = $request->session()->get('employee', []);
        $turmas = Turma::with('curso')->orderBy('nome_turma')->get();
        $cursos = Curso::orderBy('nome_curso')->get();
        $cargos = Cargo::orderBy('Nome_cargo')->get();
        $funcionarios = Funcionario::with('cargo')->orderBy('Nome')->get();
        $stockCriticalThreshold = (int) config('senaistock.low_stock_threshold', 8);
        $alerts = $this->alertsFor($books, $teacherRequests, $purchaseOrders, $stockCriticalThreshold);

        return view('senai-stock.index', [
            'activeView' => $view,
            'activeTab' => in_array($request->string('tab')->toString(), ['entrada', 'saida', 'historico', 'nova', 'aprovacoes'], true)
                ? $request->string('tab')->toString()
                : null,
            'initialRequestBookId' => $request->integer('book_id') ?: null,
            'navigationItems' => $navigationItems,
            'employee' => $employee,
            'employeeRole' => $employeeRole,
            'permissions' => $permissions,
            'books' => $books,
            'purchaseOrders' => $purchaseOrders,
            'purchaseCart' => $purchaseCart,
            'teacherRequests' => $teacherRequests,
            'professorNotifications' => $professorNotifications,
            'turmas' => $turmas,
            'cursos' => $cursos,
            'cargos' => $cargos,
            'funcionarios' => $funcionarios,
            'suppliers' => $suppliers,
            'notifications' => $notifications,
            'movements' => $movements,
            'alerts' => $alerts,
            'stockCriticalThreshold' => $stockCriticalThreshold,
            'lowStockCount' => $books->where('quantity', '<', $stockCriticalThreshold)->count(),
            'totalQuantity' => $books->sum('quantity'),
            'pendingTeacherRequests' => $teacherRequests->whereIn('status', ['pendente', 'aprovado', 'compra', 'compra_aprovada', 'separado_parcial'])->count(),
            'purchaseCartCount' => $purchaseCart->count(),
            'withdrawCartCount' => 0,
            'alertCount' => $alerts->where('severity', 'critical')->count() + $alerts->where('severity', 'warning')->count() + $notifications->whereNull('read_at')->count(),
            'supplierCount' => $suppliers->count(),
        ]);
    }

    public function receiveExisting(Request $request, Book $book, StockService $stockService): RedirectResponse
    {
        EmployeeRole::authorize($request, 'stock.receive');

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $stockService->receiveExisting(
            $book,
            (int) $data['quantity'],
            $request->session()->get('employee.id'),
            $data['notes'] ?? null
        );

        return back()->with('status', "{$data['quantity']} unidade(s) recebidas para {$book->title}.");
    }

    public function receiveViaApi(Request $request, Book $book): JsonResponse
    {
        EmployeeRole::authorize($request, 'stock.receive');

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($book, $data, $request): void {
            $book->increment('quantity', (int) $data['quantity']);

            Movement::create([
                'type' => 'entrada',
                'book_id' => $book->id,
                'funcionario_id' => $request->session()->get('employee.id'),
                'quantity' => (int) $data['quantity'],
                'justification' => $data['notes'] ?: 'Recebimento de material existente.',
            ]);
        });

        return response()->json([
            'message' => "{$data['quantity']} unidade(s) recebidas para {$book->title}.",
        ]);
    }

    public function withdrawViaApi(Request $request, Book $book): JsonResponse
    {
        EmployeeRole::authorize($request, 'stock.withdraw');

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'justification' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($book, $data, $request): void {
            if ((int) $data['quantity'] > $book->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Saldo insuficiente para {$book->title}. Disponível: {$book->quantity}.",
                ]);
            }

            $book->decrement('quantity', (int) $data['quantity']);

            Movement::create([
                'type' => 'saida',
                'book_id' => $book->id,
                'funcionario_id' => $request->session()->get('employee.id'),
                'quantity' => (int) $data['quantity'],
                'justification' => $data['justification'],
            ]);
        });

        return response()->json([
            'message' => "{$data['quantity']} unidade(s) retiradas do estoque de {$book->title}.",
        ]);
    }

    public function storeNewMaterial(Request $request, StockService $stockService): RedirectResponse
    {
        $role = EmployeeRole::fromSession($request);
        if (! EmployeeRole::can($role, 'stock.store_new')) {
            abort(403, 'Seu cargo não tem permissão para cadastrar novos materiais.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:100', 'unique:books,isbn'],
            'subject' => ['required', 'string', 'max:255', 'exists:cursos,nome_curso'],
            'quantity' => ['required', 'integer', 'min:1'],
            'minimum_stock' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => ['nullable', 'in:ativo,inativo'],
            'description' => ['nullable', 'string', 'max:1000'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('book-covers', 'public');
        }

        $book = $stockService->createBookWithOpeningStock($data, $request->session()->get('employee.id'));

        return redirect()
            ->route('senai.dashboard', ['view' => 'book_registration'])
            ->with('status', "Novo material cadastrado: {$book->title}.");
    }

    public function updateBook(Request $request, Book $book): RedirectResponse
    {
        EmployeeRole::authorize($request, 'stock.store_new');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:100', 'unique:books,isbn,'.$book->id],
            'subject' => ['required', 'string', 'max:255', 'exists:cursos,nome_curso'],
            'minimum_stock' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', 'in:ativo,inativo'],
            'description' => ['nullable', 'string', 'max:1000'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
        ]);

        if ($request->hasFile('image')) {
            if ($book->image_path) {
                Storage::disk('public')->delete($book->image_path);
            }
            $data['image_path'] = $request->file('image')->store('book-covers', 'public');
        }

        $book->update($data);

        return redirect()
            ->route('senai.dashboard', ['view' => 'book_registration'])
            ->with('status', 'Livro atualizado com sucesso.');
    }

    public function destroyBook(Request $request, Book $book): RedirectResponse
    {
        EmployeeRole::authorize($request, 'stock.store_new');

        $imagePath = $book->image_path;
        $book->delete();
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()
            ->route('senai.dashboard', ['view' => 'book_registration'])
            ->with('status', 'Livro excluido com sucesso.');
    }

    public function withdrawBatch(Request $request, StockService $stockService): RedirectResponse
    {
        EmployeeRole::authorize($request, 'stock.withdraw');

        $data = $request->validate([
            'destination' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['nullable', 'integer', 'exists:books,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $items = collect($data['items'])
            ->filter(fn ($item) => filled($item['book_id'] ?? null) && filled($item['quantity'] ?? null))
            ->map(fn ($item) => [
                'book_id' => (int) $item['book_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Adicione pelo menos um livro com quantidade para registrar a retirada.',
            ]);
        }

        $messages = [];
        foreach ($items as $item) {
            $book = Book::find($item['book_id']);
            if (! $book || $book->quantity < $item['quantity']) {
                $bookTitle = $book?->title ?? 'Livro desconhecido';
                $available = $book?->quantity ?? 0;
                $messages[$bookTitle] = "Quantidade insuficiente. Disponível: {$available}";
            }
        }

        if (! empty($messages)) {
            throw ValidationException::withMessages($messages);
        }

        $stockService->withdrawBatch($items, $data['destination'], $request->session()->get('employee.id'));

        return redirect()
            ->route('senai.dashboard', ['view' => 'movements'])
            ->with('status', 'Retirada registrada com estoque validado.');
    }

    public function fulfillTeacherRequest(Request $request, int $teacherRequest, TeacherRequestService $service): RedirectResponse
    {
        EmployeeRole::authorize($request, 'teacher_requests.fulfill');
        $teacherRequestModel = $this->findTeacherRequestModel($teacherRequest);
        $requestData = $this->findTeacherRequest($request, $teacherRequest);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $requestStatus = $teacherRequestModel?->status ?? ($requestData['status'] ?? null);

        if (! in_array($requestStatus, ['aprovado', 'separado_parcial'], true)) {
            throw ValidationException::withMessages([
                'teacher_request' => 'Aprove o pedido antes de separar os livros.',
            ]);
        }

        if (blank($requestData['bookId'] ?? null)) {
            throw ValidationException::withMessages([
                'teacher_request' => 'Este pedido ainda não está ligado a um livro do acervo.',
            ]);
        }

        $remainingQty = (int) ($requestData['remaining'] ?? $requestData['qty']);
        $requestedQty = (int) $data['quantity'];
        $availableQty = (int) ($requestData['available'] ?? 0);

        if ($requestedQty > $remainingQty) {
            throw ValidationException::withMessages([
                'quantity' => "A retirada excede o saldo pendente do pedido ({$remainingQty}).",
            ]);
        }

        if ($requestedQty > $availableQty) {
            throw ValidationException::withMessages([
                'quantity' => "Saldo insuficiente para retirar {$requestData['title']}. Disponivel: {$availableQty}.",
            ]);
        }

        DB::transaction(function () use ($teacherRequestModel, $requestData, $requestedQty, $request): void {
            $book = Book::query()->lockForUpdate()->findOrFail($requestData['bookId']);

            if ($requestedQty > $book->quantity) {
                throw ValidationException::withMessages([
                    'teacher_request' => "Saldo insuficiente para retirar {$book->title}. Disponivel: {$book->quantity}.",
                ]);
            }

            $book->decrement('quantity', $requestedQty);

            Movement::create([
                'type' => 'saida',
                'book_id' => $book->id,
                'funcionario_id' => $request->session()->get('employee.id'),
                'quantity' => $requestedQty,
                'justification' => 'Pedido do professor '.$requestData['teacher'].' para '.$requestData['turma'].'.',
            ]);

            if ($teacherRequestModel) {
                $fulfilledQuantity = min(
                    $teacherRequestModel->quantity,
                    $teacherRequestModel->fulfilled_quantity + $requestedQty
                );

                $teacherRequestModel->update([
                    'fulfilled_quantity' => $fulfilledQuantity,
                    'status' => $fulfilledQuantity >= $teacherRequestModel->quantity ? 'atendido' : 'separado_parcial',
                ]);
            }
        });

        if (! $teacherRequestModel) {
            $processed = collect($request->session()->get('processed_teacher_requests', []))
                ->push($teacherRequest)
                ->unique()
                ->values()
                ->all();

            $request->session()->put('processed_teacher_requests', $processed);
        } else {
            $freshRequest = $teacherRequestModel->fresh();
            $service->message(
                $freshRequest,
                'coordenacao',
                null,
                $freshRequest->status,
                "{$requestedQty} unidade(s) retiradas. Restam ".max($freshRequest->quantity - $freshRequest->fulfilled_quantity, 0).' unidade(s).',
                false,
                $request->session()->get('employee.id')
            );
        }

        return back()->with('status', "{$requestedQty} unidade(s) retiradas para o pedido.");
    }

    public function approveTeacherRequest(Request $request, TeacherRequest $teacherRequest, TeacherRequestService $service): RedirectResponse
    {
        EmployeeRole::authorize($request, 'purchases.approve');

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:1200'],
        ]);

        if ($teacherRequest->book_id) {
            $book = Book::find($teacherRequest->book_id);
            if ($book && $book->quantity < $teacherRequest->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Quantidade solicitada ({$teacherRequest->quantity}) excede o estoque disponível ({$book->quantity}). Considere gerar um pedido de compra.",
                ]);
            }
        }

        $service->approve($teacherRequest, $request->session()->get('employee.id'), $data['message'] ?? null);

        return back()->with('status', 'Pedido aprovado e professor notificado.');
    }

    public function rejectTeacherRequest(Request $request, TeacherRequest $teacherRequest, TeacherRequestService $service): RedirectResponse
    {
        EmployeeRole::authorizeRole($request, EmployeeRole::COORDENADOR);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
        ]);

        DB::transaction(function () use ($teacherRequest, $service, $request, $data): void {
            $teacherRequest->load('purchaseOrderItems.purchaseOrder');
            $teacherRequest->purchaseOrderItems
                ->pluck('purchaseOrder')
                ->filter()
                ->each(fn (PurchaseOrder $order) => $order->update([
                    'status' => 'rejeitado',
                    'notes' => $data['message'],
                ]));

            $service->reject($teacherRequest, $request->session()->get('employee.id'), $data['message']);
        });

        return redirect()
            ->route('senai.dashboard', ['view' => 'teacher_requests'])
            ->with('status', 'Pedido rejeitado. A justificativa já está disponível para o professor.');
    }

    public function notifyTeacherRequest(Request $request, TeacherRequest $teacherRequest, TeacherRequestService $service): RedirectResponse
    {
        EmployeeRole::authorizeRole($request, EmployeeRole::COORDENADOR);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'status' => ['nullable', 'in:aprovado,separado,atendido,rejeitado,compra'],
        ]);

        if (($data['status'] ?? null) === 'separado') {
            $service->markPrepared($teacherRequest, $request->session()->get('employee.id'), $data['message']);

            return back()->with('status', 'Professor notificado sobre a separacao do material.');
        }

        $service->message(
            $teacherRequest,
            'coordenacao',
            null,
            $data['status'] ?? $teacherRequest->status,
            $data['message'],
            true,
            $request->session()->get('employee.id')
        );

        return back()->with('status', 'Mensagem enviada ao professor.');
    }

    public function addTeacherRequestToPurchase(Request $request, int $teacherRequest): RedirectResponse
    {
        EmployeeRole::authorize($request, 'teacher_requests.purchase');

        $teacherRequestModel = $this->findTeacherRequestModel($teacherRequest);
        $requestData = $this->findTeacherRequest($request, $teacherRequest);
        $missing = max((int) $requestData['missing'], 1);
        $teacherRequestModel?->update(['status' => 'compra']);

        return redirect()
            ->route('senai.dashboard', [
                'view' => 'purchases',
                'tab' => 'nova',
                'book_id' => $requestData['bookId'],
                'quantity' => $missing,
                'justification' => 'Compra faltante para atender '.$requestData['teacher'].' / '.$requestData['turma'].'.',
            ])
            ->with('status', 'Livro e quantidade preenchidos para gerar a compra.');
    }

    public function storeTeacherRequest(Request $request, TeacherRequestService $service): RedirectResponse
    {
        $role = EmployeeRole::fromSession($request);
        $employee = $request->session()->get('employee', []);

        if (! EmployeeRole::can($role, 'teacher_requests.create')) {
            abort(403, 'Seu cargo não tem permissão para criar pedidos.');
        }

        $data = $request->validate(
            [
                'turma_id' => ['required', 'integer', 'exists:turmas,id'],
                'curso_id' => ['required', 'integer', 'exists:cursos,id'],
                'book_id' => ['required', 'integer', 'exists:books,id'],
                'quantity' => ['required', 'integer', 'min:1'],
                'due_date' => ['nullable', 'date', 'after_or_equal:today'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            [
                'due_date.date' => 'O prazo desejado deve conter uma data válida.',
                'due_date.after_or_equal' => 'O prazo desejado deve ser igual ou posterior à data de hoje.',
            ]
        );

        $book = Book::findOrFail($data['book_id']);
        $turma = Turma::with('curso')->findOrFail($data['turma_id']);

        if ($turma->curso_id !== (int) $data['curso_id']) {
            throw ValidationException::withMessages([
                'turma_id' => 'A turma selecionada não pertence ao curso informado.',
            ]);
        }

        if ($this->normalizedCourseName($book->subject) !== $this->normalizedCourseName($turma->curso?->nome_curso)) {
            throw ValidationException::withMessages([
                'book_id' => 'Selecione um livro relacionado ao curso da turma.',
            ]);
        }

        $requesterName = $employee['name'] ?? ($role === EmployeeRole::PROFESSOR ? 'Professor' : 'Coordenador');

        $teacherRequest = $service->create([
            'requested_by_funcionario_id' => $employee['id'] ?? null,
            'teacher_name' => $requesterName,
            'teacher_email' => null,
            'class_name' => $turma->nome_turma,
            'course_name' => $turma->curso?->nome_curso,
            'book_id' => $book->id,
            'quantity' => (int) $data['quantity'],
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($role === EmployeeRole::COORDENADOR && $teacherRequest->status === 'pendente') {
            $service->approve($teacherRequest, $employee['id'] ?? null, 'Pedido criado e aprovado automaticamente pela coordenação.');
        }

        return redirect()
            ->route('senai.dashboard', ['view' => 'teacher_requests'])
            ->with('status', $role === EmployeeRole::COORDENADOR
                ? 'Pedido registrado e aprovado automaticamente.'
                : 'Pedido registrado para análise da coordenação.');
    }

    public function dismissTeacherRequestNotifications(Request $request, TeacherRequest $teacherRequest): RedirectResponse
    {
        $this->authorizeTeacherRequestOwner($request, $teacherRequest);
        $teacherRequest->update([
            'notifications_dismissed_at' => now(),
            'notifications_dismissed_message_id' => $teacherRequest->messages()->max('id'),
        ]);

        return redirect()
            ->route('senai.dashboard', ['view' => 'teacher_requests'])
            ->with('status', 'Atualizações deste pedido removidas.');
    }

    public function destroyTeacherRequest(Request $request, TeacherRequest $teacherRequest): RedirectResponse
    {
        $this->authorizeTeacherRequestDeletion($request, $teacherRequest);
        $teacherRequest->delete();

        return redirect()
            ->route('senai.dashboard', ['view' => 'teacher_requests'])
            ->with('status', 'Pedido excluído com sucesso.');
    }

    public function generatePurchaseOrder(Request $request): RedirectResponse
    {
        EmployeeRole::authorize($request, 'purchases.create');

        $data = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'justification' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $book = Book::findOrFail($data['book_id']);
        $normalizedItems = collect([[
            'type' => 'restock',
            'bookId' => $book->id,
            'title' => $book->title,
            'requestedQty' => (int) $data['quantity'],
            'justification' => trim((string) ($data['justification'] ?? 'Pedido de reposição de estoque.')),
        ]]);

        if (Schema::hasTable('purchase_orders')) {
            $order = DB::transaction(function () use ($normalizedItems, $data, $request): PurchaseOrder {
                $requesterId = Funcionario::whereKey($request->session()->get('employee.id'))
                    ->value('Id_funcionario');

                $order = PurchaseOrder::create([
                    'order_number' => $this->nextOrderNumber(),
                    'supplier_id' => Supplier::where('status', 'ativo')->value('id'),
                    'requested_by_funcionario_id' => $requesterId,
                    'status' => 'pendente_aprovacao',
                    'generated_at' => now(),
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($normalizedItems as $item) {
                    $order->items()->create([
                        'book_id' => $item['bookId'],
                        'title' => $item['title'],
                        'quantity' => $item['requestedQty'],
                        'type' => $item['type'],
                        'justification' => $item['justification'],
                    ]);
                }

                return $order;
            });

            return redirect()
                ->route('senai.dashboard', ['view' => 'purchases', 'tab' => 'historico'])
                ->with('status', "Planilha {$order->order_number} gerada e enviada para o histórico.");
        }

        $order = [
            'orderId' => $this->nextOrderNumber(),
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'status' => 'pendente_aprovacao',
            'items' => $normalizedItems->all(),
        ];

        $orders = collect($request->session()->get('purchase_orders', []))
            ->prepend($order)
            ->values()
            ->all();

        $request->session()->put('purchase_orders', $orders);

        return redirect()
            ->route('senai.dashboard', ['view' => 'purchases', 'tab' => 'historico'])
            ->with('status', "Planilha {$order['orderId']} gerada e enviada para o histórico.");
    }

    public function markPurchaseOrderDelivered(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $role = EmployeeRole::fromSession($request);
        if (! EmployeeRole::can($role, 'purchases.deliver')) {
            abort(403, 'Seu cargo não tem permissão para esta ação.');
        }

        if ($purchaseOrder->status !== 'aprovado') {
            throw ValidationException::withMessages([
                'purchase_order' => 'Este pedido precisa ser aprovado pelo coordenador antes do recebimento.',
            ]);
        }

        $purchaseOrder->load('items');

        foreach ($purchaseOrder->items as $item) {
            $remaining = max($item->quantity - $item->received_quantity, 0);
            if ($remaining > 0) {
                $this->receivePurchaseItem($request, $purchaseOrder, $item, $remaining);
            }
        }

        return redirect()
            ->route('senai.dashboard', ['view' => 'purchases'])
            ->with('status', "Ordem {$purchaseOrder->order_number} marcada como entregue.");
    }

    public function receivePurchaseOrderItem(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderItem $purchaseOrderItem, TeacherRequestService $service): RedirectResponse
    {
        EmployeeRole::authorize($request, 'purchases.deliver');

        if ($purchaseOrderItem->purchase_order_id !== $purchaseOrder->id) {
            abort(404);
        }

        if (! in_array($purchaseOrder->status, ['aprovado', 'recebimento_parcial'], true)) {
            throw ValidationException::withMessages([
                'purchase_order' => 'Este pedido precisa ser aprovado antes do recebimento.',
            ]);
        }

        $remaining = max($purchaseOrderItem->quantity - $purchaseOrderItem->received_quantity, 0);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$remaining],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->receivePurchaseItem(
            $request,
            $purchaseOrder,
            $purchaseOrderItem,
            (int) $data['quantity'],
            $data['notes'] ?? null
        );

        if ($purchaseOrderItem->teacherRequest) {
            $linkedRequest = $purchaseOrderItem->teacherRequest->fresh();
            $service->message(
                $linkedRequest,
                'coordenacao',
                null,
                $linkedRequest->status,
                "{$data['quantity']} unidade(s) da compra foram recebidas para o seu pedido.",
                false,
                $request->session()->get('employee.id')
            );
        }

        return redirect()
            ->route('senai.dashboard', ['view' => 'stock', 'tab' => 'entrada'])
            ->with('status', "{$data['quantity']} unidade(s) recebidas da ordem {$purchaseOrder->order_number}.");
    }

    public function approvePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder, TeacherRequestService $service): RedirectResponse
    {
        $role = EmployeeRole::fromSession($request);
        if (! EmployeeRole::can($role, 'purchases.approve')) {
            abort(403, 'Seu cargo não tem permissão para aprovar pedidos de compra.');
        }

        if ($purchaseOrder->status !== 'pendente_aprovacao' && $purchaseOrder->status !== 'aguardando') {
            throw ValidationException::withMessages([
                'purchase_order' => 'Este pedido não está aguardando aprovação.',
            ]);
        }

        DB::transaction(function () use ($purchaseOrder, $service, $request): void {
            $purchaseOrder->update(['status' => 'aprovado']);
            $purchaseOrder->load('items.teacherRequest');

            $purchaseOrder->items
                ->pluck('teacherRequest')
                ->filter()
                ->each(function (TeacherRequest $teacherRequest) use ($service, $request): void {
                    $remaining = max($teacherRequest->quantity - $teacherRequest->fulfilled_quantity, 0);
                    $readyToSeparate = $teacherRequest->book?->quantity >= $remaining;

                    $teacherRequest->update([
                        'status' => $readyToSeparate ? 'aprovado' : 'compra_aprovada',
                        'approved_at' => now(),
                    ]);
                    $service->message(
                        $teacherRequest,
                        'coordenacao',
                        null,
                        $readyToSeparate ? 'aprovado' : 'compra_aprovada',
                        $readyToSeparate
                            ? 'A compra foi aprovada e o estoque atual já permite separar o pedido.'
                            : 'A compra necessária para o seu pedido foi aprovada.',
                        false,
                        $request->session()->get('employee.id')
                    );
                });
        });

        return redirect()
            ->route('senai.dashboard', ['view' => 'purchases'])
            ->with('status', "Pedido {$purchaseOrder->order_number} aprovado para compra.");
    }

    public function storeTurma(Request $request): RedirectResponse
    {
        $role = EmployeeRole::fromSession($request);
        if (! EmployeeRole::can($role, 'classes.manage')) {
            abort(403, 'Seu cargo não tem permissão para cadastrar turmas.');
        }

        $data = $request->validate([
            'nome_turma' => ['required', 'string', 'max:255'],
            'curso_id' => ['required', 'integer', 'exists:cursos,id'],
        ]);

        Turma::create([
            'nome_turma' => $data['nome_turma'],
            'curso_id' => $data['curso_id'],
        ]);

        return redirect()
            ->route('senai.dashboard', ['view' => 'classes'])
            ->with('status', 'Turma cadastrada com sucesso.');
    }

    public function updateTurma(Request $request, Turma $turma): RedirectResponse
    {
        EmployeeRole::authorize($request, 'classes.manage');

        $data = $request->validate([
            'nome_turma' => ['required', 'string', 'max:255'],
            'curso_id' => ['required', 'integer', 'exists:cursos,id'],
        ]);

        $turma->update($data);

        return redirect()
            ->route('senai.dashboard', ['view' => 'classes'])
            ->with('status', 'Turma atualizada com sucesso.');
    }

    public function destroyTurma(Request $request, Turma $turma): RedirectResponse
    {
        EmployeeRole::authorize($request, 'classes.manage');
        $turma->delete();

        return redirect()
            ->route('senai.dashboard', ['view' => 'classes'])
            ->with('status', 'Turma excluida com sucesso.');
    }

    public function storeCurso(Request $request): RedirectResponse
    {
        EmployeeRole::authorize($request, 'classes.manage');

        $data = $request->validate([
            'nome_curso' => ['required', 'string', 'max:255', 'unique:cursos,nome_curso'],
        ]);

        Curso::create($data);

        return redirect()
            ->route('senai.dashboard', ['view' => 'courses'])
            ->with('status', 'Curso cadastrado com sucesso.');
    }

    public function updateCurso(Request $request, Curso $curso): RedirectResponse
    {
        EmployeeRole::authorize($request, 'classes.manage');

        $data = $request->validate([
            'nome_curso' => ['required', 'string', 'max:255', 'unique:cursos,nome_curso,'.$curso->id],
        ]);

        $oldName = $curso->nome_curso;
        DB::transaction(function () use ($curso, $data, $oldName): void {
            $curso->update($data);
            Book::where('subject', $oldName)->update(['subject' => $data['nome_curso']]);
        });

        return redirect()
            ->route('senai.dashboard', ['view' => 'courses'])
            ->with('status', 'Curso atualizado com sucesso.');
    }

    public function destroyCurso(Request $request, Curso $curso): RedirectResponse
    {
        EmployeeRole::authorize($request, 'classes.manage');

        if ($curso->turmas()->exists() || Book::where('subject', $curso->nome_curso)->exists()) {
            throw ValidationException::withMessages([
                'curso' => 'Remova ou mova as turmas e livros deste curso antes de exclui-lo.',
            ]);
        }

        $curso->delete();

        return redirect()
            ->route('senai.dashboard', ['view' => 'courses'])
            ->with('status', 'Curso excluido com sucesso.');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'lead_time_days' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        Supplier::create($data + ['status' => 'ativo']);

        return redirect()
            ->route('senai.dashboard', ['view' => 'suppliers'])
            ->with('status', 'Fornecedor cadastrado.');
    }

    public function updateSupplierStatus(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:ativo,inativo'],
        ]);

        $supplier->update(['status' => $data['status']]);

        return back()->with('status', 'Status do fornecedor atualizado.');
    }

    public function addCriticalBookToCart(Request $request, Book $book): RedirectResponse
    {
        EmployeeRole::authorize($request, 'alerts.purchase');

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $threshold = (int) config('senaistock.low_stock_threshold', 8);
        $suggestedQuantity = (int) ($data['quantity'] ?? max($threshold * 2 - $book->quantity, 1));

        return redirect()
            ->route('senai.dashboard', [
                'view' => 'purchases',
                'tab' => 'nova',
                'book_id' => $book->id,
                'quantity' => $suggestedQuantity,
                'justification' => 'Reposição preventiva: estoque atual em '.$book->quantity.' unidade(s).',
            ])
            ->with('status', 'Livro e quantidade preenchidos para gerar a compra.');
    }

    private function presentBook(Book $book): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author ?: 'Não informado',
            'publisher' => $book->publisher ?: 'Não informado',
            'year' => $book->publication_year,
            'pages' => $book->pages,
            'isbn' => $book->isbn,
            'subject' => $book->subject ?: 'Geral',
            'quantity' => (int) $book->quantity,
            'minimumStock' => (int) ($book->minimum_stock ?? config('senaistock.low_stock_threshold', 8)),
            'location' => $book->location ?: 'Estoque central',
            'imageUrl' => $book->image_path ? Storage::url($book->image_path) : null,
            'status' => $book->status ?: 'ativo',
            'desc' => $book->description ?: 'Material didatico usado para aulas, reposicoes e retiradas controladas pela coordenação.',
        ];
    }

    private function teacherRequestsFor(Request $request, Collection $books, array $processedTeacherRequests = []): Collection
    {
        if (Schema::hasTable('teacher_requests') && TeacherRequest::query()->exists()) {
            $query = TeacherRequest::with(['book', 'messages', 'purchaseOrderItems.purchaseOrder']);

            if (EmployeeRole::fromSession($request) === EmployeeRole::PROFESSOR) {
                $query->where('requested_by_funcionario_id', $request->session()->get('employee.id'));
            }

            return $query
                ->latest()
                ->get()
                ->map(fn (TeacherRequest $teacherRequest) => $this->presentTeacherRequest($teacherRequest, $books))
                ->values();
        }

        if ($books->isEmpty()) {
            return collect(config('senaistock.teacher_requests', []));
        }

        $templates = [
            ['teacher' => 'Prof. Carlos Mendes', 'email' => 'carlos.mendes@escola.senai.br', 'turma' => 'MEC-2A'],
            ['teacher' => 'Profa. Ana Paula', 'email' => 'ana.paula@escola.senai.br', 'turma' => 'DS-1B'],
            ['teacher' => 'Prof. Roberto Alves', 'email' => 'roberto.alves@escola.senai.br', 'turma' => 'ELE-3C'],
            ['teacher' => 'Profa. Fernanda Lima', 'email' => 'fernanda.lima@escola.senai.br', 'turma' => 'ADM-1A'],
        ];

        return collect($templates)->map(function (array $template, int $index) use ($books, $processedTeacherRequests) {
            $book = $books->values()->get($index % $books->count());
            $quantity = match ($index) {
                0 => max(1, min((int) $book['quantity'], 18)),
                1 => (int) $book['quantity'] + 5,
                2 => max(1, min((int) $book['quantity'], 45)),
                default => (int) $book['quantity'] + 10,
            };
            $id = 201 + $index;
            $isProcessed = in_array($id, $processedTeacherRequests, true);
            $available = (int) $book['quantity'];

            return [
                'id' => $id,
                'teacher' => $template['teacher'],
                'email' => $template['email'],
                'turma' => $template['turma'],
                'subject' => $book['subject'],
                'bookId' => $book['id'],
                'title' => $book['title'],
                'qty' => $quantity,
                'available' => $available,
                'missing' => max($quantity - $available, 0),
                'status' => $isProcessed || $index === 2 ? 'atendido' : 'pendente',
                'date' => now()->subDays($index)->format('d/m/Y'),
                'time' => now()->subMinutes(45 * ($index + 1))->format('H:i'),
                'notes' => null,
            ];
        });
    }

    private function presentTeacherRequest(TeacherRequest $teacherRequest, Collection $books): array
    {
        $book = $books->firstWhere('id', $teacherRequest->book_id);
        $available = (int) ($book['quantity'] ?? $teacherRequest->book?->quantity ?? 0);
        $quantity = (int) $teacherRequest->quantity;
        $fulfilled = (int) $teacherRequest->fulfilled_quantity;

        return [
            'id' => $teacherRequest->id,
            'persisted' => true,
            'requestedBy' => $teacherRequest->requested_by_funcionario_id,
            'protocol' => $teacherRequest->protocol,
            'teacher' => $teacherRequest->teacher_name,
            'email' => $teacherRequest->teacher_email,
            'turma' => $teacherRequest->class_name,
            'course' => $teacherRequest->course_name,
            'subject' => $teacherRequest->subject ?: ($book['subject'] ?? 'Geral'),
            'bookId' => $teacherRequest->book_id,
            'title' => $teacherRequest->title ?: ($book['title'] ?? 'Material solicitado'),
            'qty' => $quantity,
            'fulfilled' => $fulfilled,
            'remaining' => max($quantity - $fulfilled, 0),
            'available' => $available,
            'missing' => max(($quantity - $fulfilled) - $available, 0),
            'status' => $teacherRequest->status,
            'date' => optional($teacherRequest->created_at)->format('d/m/Y') ?? now()->format('d/m/Y'),
            'time' => optional($teacherRequest->created_at)->format('H:i') ?? now()->format('H:i'),
            'dueDate' => optional($teacherRequest->due_date)->format('d/m/Y'),
            'dueDateSort' => $teacherRequest->due_date?->format('Y-m-d'),
            'notes' => $teacherRequest->notes,
            'lastMessage' => $teacherRequest->messages->sortByDesc('created_at')->first()?->message,
            'rejectionReason' => $teacherRequest->messages
                ->where('status', 'rejeitado')
                ->sortByDesc('created_at')
                ->first()?->message,
            'purchaseOrderId' => $teacherRequest->purchaseOrderItems
                ->pluck('purchaseOrder')
                ->filter()
                ->first()?->id,
            'purchaseOrderStatus' => $teacherRequest->purchaseOrderItems
                ->pluck('purchaseOrder')
                ->filter()
                ->first()?->status,
        ];
    }

    private function purchaseOrdersFor(Request $request): Collection
    {
        $databaseOrders = collect();

        if (Schema::hasTable('purchase_orders')) {
            $databaseOrders = PurchaseOrder::with([
                'supplier',
                'requester',
                'items.book',
                'items.teacherRequest.requester',
            ])
                ->latest('generated_at')
                ->latest()
                ->get()
                ->map(fn (PurchaseOrder $order) => [
                    'id' => $order->id,
                    'orderId' => $order->order_number,
                    'supplier' => $order->supplier?->name,
                    'date' => optional($order->generated_at ?? $order->created_at)->format('d/m/Y') ?? now()->format('d/m/Y'),
                    'time' => optional($order->generated_at ?? $order->created_at)->format('H:i') ?? now()->format('H:i'),
                    'status' => $order->status,
                    'notes' => $order->notes,
                    'requestedBy' => $order->requester?->Nome,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'type' => $item->type,
                        'bookId' => $item->book_id,
                        'title' => $item->title,
                        'isbn' => $item->book?->isbn,
                        'subject' => $item->book?->subject,
                        'requestedQty' => $item->quantity,
                        'receivedQty' => $item->received_quantity,
                        'remainingQty' => max($item->quantity - $item->received_quantity, 0),
                        'justification' => $item->justification,
                        'teacherRequest' => $item->teacherRequest?->protocol,
                        'teacherName' => $item->teacherRequest?->teacher_name
                            ?? $item->teacherRequest?->requester?->Nome,
                        'className' => $item->teacherRequest?->class_name,
                        'courseName' => $item->teacherRequest?->course_name,
                        'dueDate' => $item->teacherRequest?->due_date?->format('d/m/Y'),
                    ])->values()->all(),
                ]);
        }

        $sessionPurchaseOrders = collect($request->session()->get('purchase_orders', []));

        return $databaseOrders
            ->concat($sessionPurchaseOrders)
            ->concat(collect(config('senaistock.purchase_orders', [])))
            ->values();
    }

    private function suppliersFor(): Collection
    {
        if (! Schema::hasTable('suppliers')) {
            return collect();
        }

        return Supplier::withCount('purchaseOrders')
            ->orderByRaw("case when status = 'ativo' then 0 else 1 end")
            ->orderBy('name')
            ->get();
    }

    private function alertsFor(Collection $books, Collection $teacherRequests, Collection $purchaseOrders, int $threshold): Collection
    {
        $lowStockAlerts = $books
            ->filter(fn ($book) => (int) $book['quantity'] < $threshold)
            ->map(fn ($book) => [
                'type' => 'stock',
                'severity' => 'critical',
                'title' => $book['title'],
                'message' => 'Saldo atual: '.$book['quantity'].' unidade(s).',
                'action' => 'Comprar reposicao',
                'bookId' => $book['id'],
            ]);

        $requestAlerts = $teacherRequests
            ->where('status', 'pendente')
            ->filter(fn ($request) => (int) ($request['missing'] ?? 0) > 0)
            ->map(fn ($request) => [
                'type' => 'request',
                'severity' => 'warning',
                'title' => $request['teacher'].' / '.$request['turma'],
                'message' => 'Faltam '.$request['missing'].' un de '.$request['title'].'.',
                'action' => 'Enviar para compras',
                'requestId' => $request['id'],
            ]);

        $orderAlerts = $purchaseOrders
            ->whereIn('status', ['pendente_aprovacao', 'aguardando', 'aprovado'])
            ->take(5)
            ->map(fn ($order) => [
                'type' => 'purchase',
                'severity' => 'info',
                'title' => $order['orderId'],
                'message' => 'Pedido aguardando entrega desde '.$order['date'].'.',
                'action' => 'Ver histórico',
            ]);

        return $lowStockAlerts
            ->concat($requestAlerts)
            ->concat($orderAlerts)
            ->values();
    }

    private function findTeacherRequest(Request $request, int $teacherRequest): array
    {
        $books = Book::query()
            ->orderBy('subject')
            ->orderBy('title')
            ->get()
            ->map(fn (Book $book) => $this->presentBook($book))
            ->values();

        $requestModel = $this->findTeacherRequestModel($teacherRequest);

        if ($requestModel) {
            return $this->presentTeacherRequest($requestModel->load('book'), $books);
        }

        $requestData = $this->teacherRequestsFor(
            $request,
            $books,
            $request->session()->get('processed_teacher_requests', [])
        )->firstWhere('id', $teacherRequest);

        if (! $requestData) {
            abort(404);
        }

        return $requestData;
    }

    private function findTeacherRequestModel(int $teacherRequest): ?TeacherRequest
    {
        if (! Schema::hasTable('teacher_requests')) {
            return null;
        }

        return TeacherRequest::find($teacherRequest);
    }

    private function authorizeTeacherRequestOwner(Request $request, TeacherRequest $teacherRequest): void
    {
        if ((int) $teacherRequest->requested_by_funcionario_id !== (int) $request->session()->get('employee.id')) {
            abort(403, 'Você só pode excluir seus próprios pedidos e notificações.');
        }
    }

    private function authorizeTeacherRequestDeletion(Request $request, TeacherRequest $teacherRequest): void
    {
        if (EmployeeRole::fromSession($request) === EmployeeRole::COORDENADOR) {
            return;
        }

        $this->authorizeTeacherRequestOwner($request, $teacherRequest);

        if (! $teacherRequest->isPending()) {
            abort(403, 'Pedidos aprovados ou processados não podem ser excluídos pelo professor.');
        }
    }

    private function canAccessView(string $view, string $roleKey): bool
    {
        $role = match ($roleKey) {
            'professor' => EmployeeRole::PROFESSOR,
            'coordenador' => EmployeeRole::COORDENADOR,
            default => null,
        };

        return EmployeeRole::canAccessView($role, $view);
    }

    private function roleKey(?string $role): string
    {
        return Str::of($role ?? '')
            ->ascii()
            ->lower()
            ->replace(' ', '_')
            ->toString();
    }

    private function normalizedCourseName(?string $name): string
    {
        return Str::of($name ?? '')
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function nextOrderNumber(): string
    {
        return 'PED-'.now()->format('ymd-His').'-'.Str::upper(Str::random(3));
    }

    private function receivePurchaseItem(
        Request $request,
        PurchaseOrder $purchaseOrder,
        PurchaseOrderItem $item,
        int $quantity,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($request, $purchaseOrder, $item, $quantity, $notes): void {
            $lockedItem = PurchaseOrderItem::query()->lockForUpdate()->findOrFail($item->id);
            $remaining = max($lockedItem->quantity - $lockedItem->received_quantity, 0);

            if ($quantity > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' => "Quantidade maior que o saldo pendente da compra ({$remaining}).",
                ]);
            }

            $book = Book::query()->lockForUpdate()->findOrFail($lockedItem->book_id);
            $book->increment('quantity', $quantity);
            $lockedItem->increment('received_quantity', $quantity);

            Movement::create([
                'type' => 'entrada',
                'book_id' => $book->id,
                'funcionario_id' => $request->session()->get('employee.id'),
                'quantity' => $quantity,
                'justification' => $notes ?: 'Recebimento parcial da ordem '.$purchaseOrder->order_number.'.',
            ]);

            $purchaseOrder->load('items');
            $completed = $purchaseOrder->items->every(
                fn (PurchaseOrderItem $orderItem) => $orderItem->received_quantity >= $orderItem->quantity
            );
            $purchaseOrder->update(['status' => $completed ? 'entregue' : 'recebimento_parcial']);

            if ($lockedItem->teacherRequest) {
                $requestRemaining = max(
                    $lockedItem->teacherRequest->quantity - $lockedItem->teacherRequest->fulfilled_quantity,
                    0
                );

                $lockedItem->teacherRequest->update([
                    'status' => $book->fresh()->quantity >= $requestRemaining ? 'aprovado' : 'compra_aprovada',
                ]);
            }
        });
    }
}
