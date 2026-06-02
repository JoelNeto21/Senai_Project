<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cargo;
use App\Models\Funcionario;
use App\Models\Movement;
use App\Models\PurchaseOrder;
use App\Models\StockNotification;
use App\Models\Supplier;
use App\Models\TeacherRequest;
use App\Models\Turma;
use App\Services\StockService;
use App\Services\TeacherRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        'receive',
        'withdraw',
        'movements',
        'alerts',
        'notifications',
        'suppliers',
        'classes',
        'people',
    ];

    public function index(Request $request, string $view = 'insights'): View
    {
        if (!in_array($view, self::VALID_VIEWS, true)) {
            abort(404);
        }

        $employee = $request->session()->get('employee', []);
        $roleKey = $employee['role_key'] ?? $this->roleKey($employee['cargo'] ?? null);

        if (!$this->canAccessView($view, $roleKey)) {
            abort(403);
        }

        $books = Book::query()
            ->orderBy('subject')
            ->orderBy('title')
            ->get()
            ->map(fn (Book $book) => $this->presentBook($book))
            ->values();

        if ($books->isEmpty()) {
            $books = collect(config('senaistock.books', []));
        }

        $processedTeacherRequests = collect($request->session()->get('processed_teacher_requests', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        $teacherRequests = $this->teacherRequestsFor($books, $processedTeacherRequests);
        $purchaseCart = collect($request->session()->get('purchase_cart', []))->values();
        $purchaseOrders = $this->purchaseOrdersFor($request);
        $suppliers = $this->suppliersFor();
        $notifications = StockNotification::with(['teacherRequest', 'book'])
            ->latest()
            ->limit(40)
            ->get();
        $movements = Movement::with(['book', 'funcionario'])
            ->latest()
            ->limit(60)
            ->get();

        $navigationItems = collect(config('senaistock.navigation_items', []))
            ->filter(fn (array $item) => $this->canAccessView($item['id'], $roleKey))
            ->values()
            ->all();
        $turmas = Turma::with('curso')->orderBy('nome_turma')->get();
        $cargos = Cargo::orderBy('Nome_cargo')->get();
        $funcionarios = Funcionario::with('cargo')->orderBy('Nome')->get();
        $stockCriticalThreshold = (int) config('senaistock.low_stock_threshold', 8);
        $alerts = $this->alertsFor($books, $teacherRequests, $purchaseOrders, $stockCriticalThreshold);

        return view('senai-stock.index', [
            'activeView' => $view,
            'navigationItems' => $navigationItems,
            'employee' => $employee,
            'books' => $books,
            'purchaseOrders' => $purchaseOrders,
            'purchaseCart' => $purchaseCart,
            'teacherRequests' => $teacherRequests,
            'turmas' => $turmas,
            'cargos' => $cargos,
            'funcionarios' => $funcionarios,
            'suppliers' => $suppliers,
            'notifications' => $notifications,
            'movements' => $movements,
            'alerts' => $alerts,
            'stockCriticalThreshold' => $stockCriticalThreshold,
            'lowStockCount' => $books->where('quantity', '<', $stockCriticalThreshold)->count(),
            'totalQuantity' => $books->sum('quantity'),
            'pendingTeacherRequests' => $teacherRequests->where('status', 'pendente')->count(),
            'purchaseCartCount' => $purchaseCart->count(),
            'withdrawCartCount' => 0,
            'alertCount' => $alerts->where('severity', 'critical')->count() + $alerts->where('severity', 'warning')->count() + $notifications->whereNull('read_at')->count(),
            'supplierCount' => $suppliers->count(),
        ]);
    }

    public function receiveExisting(Request $request, Book $book, StockService $stockService): RedirectResponse
    {
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

    public function storeNewMaterial(Request $request, StockService $stockService): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:100', 'unique:books,isbn'],
            'subject' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'minimum_stock' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:ativo,inativo'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $book = $stockService->createBookWithOpeningStock($data, $request->session()->get('employee.id'));

        return redirect()
            ->route('senai.dashboard', ['view' => 'library'])
            ->with('status', "Novo material cadastrado: {$book->title}.");
    }

    public function withdrawBatch(Request $request, StockService $stockService): RedirectResponse
    {
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
            if (!$book || $book->quantity < $item['quantity']) {
                $bookTitle = $book?->title ?? 'Livro desconhecido';
                $available = $book?->quantity ?? 0;
                $messages[$bookTitle] = "Quantidade insuficiente. Disponível: {$available}";
            }
        }

        if (!empty($messages)) {
            throw ValidationException::withMessages($messages);
        }

        $stockService->withdrawBatch($items, $data['destination'], $request->session()->get('employee.id'));

        return redirect()
            ->route('senai.dashboard', ['view' => 'movements'])
            ->with('status', 'Retirada registrada com estoque validado.');
    }

    public function fulfillTeacherRequest(Request $request, int $teacherRequest, TeacherRequestService $service): RedirectResponse
    {
        $teacherRequestModel = $this->findTeacherRequestModel($teacherRequest);
        $requestData = $this->findTeacherRequest($request, $teacherRequest);

        if (blank($requestData['bookId'] ?? null)) {
            throw ValidationException::withMessages([
                'teacher_request' => 'Este pedido ainda nao esta ligado a um livro do acervo.',
            ]);
        }

        if ($teacherRequestModel) {
            $service->fulfill($teacherRequestModel->load('book'), $request->session()->get('employee.id'));
        }

        if (!$teacherRequestModel) {
            $processed = collect($request->session()->get('processed_teacher_requests', []))
                ->push($teacherRequest)
                ->unique()
                ->values()
                ->all();

            $request->session()->put('processed_teacher_requests', $processed);
        }

        return back()->with('status', 'Pedido separado e estoque atualizado.');
    }

    public function approveTeacherRequest(Request $request, TeacherRequest $teacherRequest, TeacherRequestService $service): RedirectResponse
    {
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
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $service->reject($teacherRequest, $request->session()->get('employee.id'), $data['message']);

        return back()->with('status', 'Pedido rejeitado com justificativa registrada.');
    }

    public function notifyTeacherRequest(Request $request, TeacherRequest $teacherRequest, TeacherRequestService $service): RedirectResponse
    {
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
            'almoxarifado',
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
        $teacherRequestModel = $this->findTeacherRequestModel($teacherRequest);
        $requestData = $this->findTeacherRequest($request, $teacherRequest);
        $missing = max((int) $requestData['missing'], 1);
        $cart = collect($request->session()->get('purchase_cart', []));

        $cart->push([
            'id' => 'teacher-' . $teacherRequest . '-' . Str::random(5),
            'type' => 'restock',
            'bookId' => $requestData['bookId'],
            'title' => $requestData['title'],
            'quantity' => $missing,
            'justification' => 'Compra faltante para atender ' . $requestData['teacher'] . ' / ' . $requestData['turma'] . '.',
        ]);

        $request->session()->put('purchase_cart', $cart->values()->all());
        $teacherRequestModel?->update(['status' => 'compra']);

        return redirect()
            ->route('senai.dashboard', ['view' => 'purchases'])
            ->with('status', "{$missing} unidade(s) adicionadas ao carrinho de compras.");
    }

    public function storeTeacherRequest(Request $request, TeacherRequestService $service): RedirectResponse
    {
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'teacher_email' => ['nullable', 'email', 'max:255'],
            'class_name' => ['required', 'string', 'max:255'],
            'course_name' => ['nullable', 'string', 'max:255'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->create($data);

        return redirect()
            ->route('senai.dashboard', ['view' => 'teacher_requests'])
            ->with('status', 'Pedido registrado na fila do almoxarifado.');
    }

    public function generatePurchaseOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'string'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $items = json_decode($data['items'], true);

        if (!is_array($items) || empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Adicione pelo menos um item antes de gerar a planilha de pedido.',
            ]);
        }

        $normalizedItems = collect($items)
            ->filter(fn ($item) => filled($item['title'] ?? null) && (int) ($item['quantity'] ?? 0) > 0)
            ->map(fn ($item) => [
                'type' => ($item['type'] ?? 'restock') === 'new' ? 'new' : 'restock',
                'bookId' => filled($item['bookId'] ?? null) ? (int) $item['bookId'] : null,
                'title' => trim((string) $item['title']),
                'requestedQty' => (int) $item['quantity'],
                'justification' => trim((string) ($item['justification'] ?? 'Pedido de reposicao.')),
            ])
            ->values();

        if ($normalizedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Os itens do pedido estao incompletos.',
            ]);
        }

        if (Schema::hasTable('purchase_orders')) {
            $order = DB::transaction(function () use ($normalizedItems, $data, $request): PurchaseOrder {
                $order = PurchaseOrder::create([
                    'order_number' => $this->nextOrderNumber(),
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'requested_by_funcionario_id' => $request->session()->get('employee.id'),
                    'status' => 'aguardando',
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

            $request->session()->forget('purchase_cart');

            return redirect()
                ->route('senai.dashboard', ['view' => 'history'])
                ->with('status', "Planilha {$order->order_number} gerada e enviada para o historico.");
        }

        $order = [
            'orderId' => $this->nextOrderNumber(),
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'status' => 'aguardando',
            'items' => $normalizedItems->all(),
        ];

        $orders = collect($request->session()->get('purchase_orders', []))
            ->prepend($order)
            ->values()
            ->all();

        $request->session()->put('purchase_orders', $orders);
        $request->session()->forget('purchase_cart');

        return redirect()
            ->route('senai.dashboard', ['view' => 'history'])
            ->with('status', "Planilha {$order['orderId']} gerada e enviada para o historico.");
    }

    public function markPurchaseOrderDelivered(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchaseOrder): void {
            $purchaseOrder->load('items.book');

            foreach ($purchaseOrder->items as $item) {
                if (!$item->book) {
                    continue;
                }

                $item->book->increment('quantity', $item->quantity);

                Movement::create([
                    'type' => 'entrada',
                    'book_id' => $item->book->id,
                    'funcionario_id' => $request->session()->get('employee.id'),
                    'quantity' => $item->quantity,
                    'justification' => 'Recebimento da ordem ' . $purchaseOrder->order_number . '.',
                ]);
            }

            $purchaseOrder->update(['status' => 'entregue']);
        });

        return redirect()
            ->route('senai.dashboard', ['view' => 'history'])
            ->with('status', "Ordem {$purchaseOrder->order_number} marcada como entregue.");
    }

    public function addCriticalBookToCart(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $threshold = (int) config('senaistock.low_stock_threshold', 8);
        $suggestedQuantity = (int) ($data['quantity'] ?? max($threshold * 2 - $book->quantity, 1));
        $cart = collect($request->session()->get('purchase_cart', []));

        $cart->push([
            'id' => 'critical-' . $book->id . '-' . Str::random(5),
            'type' => 'restock',
            'bookId' => $book->id,
            'title' => $book->title,
            'quantity' => $suggestedQuantity,
            'justification' => 'Reposicao preventiva: estoque atual em ' . $book->quantity . ' unidade(s).',
        ]);

        $request->session()->put('purchase_cart', $cart->values()->all());

        return redirect()
            ->route('senai.dashboard', ['view' => 'purchases'])
            ->with('status', "{$suggestedQuantity} unidade(s) de {$book->title} adicionadas ao carrinho.");
    }

    private function presentBook(Book $book): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author ?: 'Não informado',
            'publisher' => $book->publisher ?: 'Não informado',
            'year' => $book->publication_year ?? (2020 + ($book->id % 5)),
            'pages' => $book->pages ?? 180,
            'isbn' => $book->isbn,
            'subject' => $book->subject ?: 'Geral',
            'quantity' => (int) $book->quantity,
            'minimumStock' => (int) ($book->minimum_stock ?? config('senaistock.low_stock_threshold', 8)),
            'location' => $book->location ?: 'Almoxarifado central',
            'status' => $book->status ?: 'ativo',
            'desc' => $book->description ?: 'Material didatico usado para aulas, reposicoes e retiradas controladas pelo almoxarifado.',
        ];
    }

    private function teacherRequestsFor(Collection $books, array $processedTeacherRequests = []): Collection
    {
        if (Schema::hasTable('teacher_requests') && TeacherRequest::query()->exists()) {
            return TeacherRequest::with(['book', 'messages'])
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

        return [
            'id' => $teacherRequest->id,
            'protocol' => $teacherRequest->protocol,
            'teacher' => $teacherRequest->teacher_name,
            'email' => $teacherRequest->teacher_email,
            'turma' => $teacherRequest->class_name,
            'course' => $teacherRequest->course_name,
            'subject' => $teacherRequest->subject ?: ($book['subject'] ?? 'Geral'),
            'bookId' => $teacherRequest->book_id,
            'title' => $teacherRequest->title ?: ($book['title'] ?? 'Material solicitado'),
            'qty' => $quantity,
            'available' => $available,
            'missing' => max($quantity - $available, 0),
            'status' => $teacherRequest->status,
            'date' => optional($teacherRequest->created_at)->format('d/m/Y') ?? now()->format('d/m/Y'),
            'time' => optional($teacherRequest->created_at)->format('H:i') ?? now()->format('H:i'),
            'dueDate' => optional($teacherRequest->due_date)->format('d/m/Y'),
            'notes' => $teacherRequest->notes,
            'lastMessage' => $teacherRequest->messages->sortByDesc('created_at')->first()?->message,
        ];
    }

    private function purchaseOrdersFor(Request $request): Collection
    {
        $databaseOrders = collect();

        if (Schema::hasTable('purchase_orders')) {
            $databaseOrders = PurchaseOrder::with(['supplier', 'items'])
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
                    'items' => $order->items->map(fn ($item) => [
                        'type' => $item->type,
                        'bookId' => $item->book_id,
                        'title' => $item->title,
                        'requestedQty' => $item->quantity,
                        'justification' => $item->justification,
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
        if (!Schema::hasTable('suppliers')) {
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
                'message' => 'Saldo atual: ' . $book['quantity'] . ' unidade(s).',
                'action' => 'Comprar reposicao',
                'bookId' => $book['id'],
            ]);

        $requestAlerts = $teacherRequests
            ->where('status', 'pendente')
            ->filter(fn ($request) => (int) ($request['missing'] ?? 0) > 0)
            ->map(fn ($request) => [
                'type' => 'request',
                'severity' => 'warning',
                'title' => $request['teacher'] . ' / ' . $request['turma'],
                'message' => 'Faltam ' . $request['missing'] . ' un de ' . $request['title'] . '.',
                'action' => 'Enviar para compras',
                'requestId' => $request['id'],
            ]);

        $orderAlerts = $purchaseOrders
            ->where('status', 'aguardando')
            ->take(5)
            ->map(fn ($order) => [
                'type' => 'purchase',
                'severity' => 'info',
                'title' => $order['orderId'],
                'message' => 'Pedido aguardando entrega desde ' . $order['date'] . '.',
                'action' => 'Ver historico',
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
            $books,
            $request->session()->get('processed_teacher_requests', [])
        )->firstWhere('id', $teacherRequest);

        if (!$requestData) {
            abort(404);
        }

        return $requestData;
    }

    private function findTeacherRequestModel(int $teacherRequest): ?TeacherRequest
    {
        if (!Schema::hasTable('teacher_requests')) {
            return null;
        }

        return TeacherRequest::find($teacherRequest);
    }

    private function canAccessView(string $view, string $roleKey): bool
    {
        $adminOnlyViews = ['classes', 'people'];

        if (in_array($view, $adminOnlyViews, true)) {
            return in_array($roleKey, ['administrador'], true);
        }

        if ($view === 'purchases') {
            return in_array($roleKey, ['administrador', 'almoxarife'], true);
        }

        return in_array($roleKey, ['administrador', 'almoxarife'], true);
    }

    private function roleKey(?string $role): string
    {
        return Str::of($role ?? '')
            ->ascii()
            ->lower()
            ->replace(' ', '_')
            ->toString();
    }

    private function nextOrderNumber(): string
    {
        return 'PED-' . now()->format('ymd-His') . '-' . Str::upper(Str::random(3));
    }
}
