<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cargo;
use App\Models\Funcionario;
use App\Models\Movement;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\TeacherRequest;
use App\Models\Turma;
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
        'suppliers',
        'classes',
        'people',
        'settings',
    ];

    public function index(Request $request, string $view = 'insights'): View
    {
        if (!in_array($view, self::VALID_VIEWS, true)) {
            abort(404);
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

        $this->ensureDemoOperationalData();

        $processedTeacherRequests = collect($request->session()->get('processed_teacher_requests', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        $teacherRequests = $this->teacherRequestsFor($books, $processedTeacherRequests);
        $purchaseCart = collect($request->session()->get('purchase_cart', []))->values();
        $purchaseOrders = $this->purchaseOrdersFor($request);
        $suppliers = $this->suppliersFor();
        $movements = Movement::with(['book', 'funcionario'])
            ->latest()
            ->limit(60)
            ->get();

        $navigationItems = config('senaistock.navigation_items', []);
        $employee = $request->session()->get('employee', []);
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
            'movements' => $movements,
            'alerts' => $alerts,
            'stockCriticalThreshold' => $stockCriticalThreshold,
            'lowStockCount' => $books->where('quantity', '<', $stockCriticalThreshold)->count(),
            'totalQuantity' => $books->sum('quantity'),
            'pendingTeacherRequests' => $teacherRequests->where('status', 'pendente')->count(),
            'purchaseCartCount' => $purchaseCart->count(),
            'withdrawCartCount' => 0,
            'alertCount' => $alerts->where('severity', 'critical')->count() + $alerts->where('severity', 'warning')->count(),
            'supplierCount' => $suppliers->count(),
        ]);
    }

    public function receiveExisting(Request $request, Book $book): RedirectResponse
    {
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

        return back()->with('status', "{$data['quantity']} unidade(s) recebidas para {$book->title}.");
    }

    public function storeNewMaterial(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:100', 'unique:books,isbn'],
            'subject' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $book = null;

        DB::transaction(function () use (&$book, $data, $request): void {
            $book = Book::create([
                'title' => $data['title'],
                'isbn' => $data['isbn'] ?: 'SEM-' . Str::upper(Str::random(8)),
                'subject' => $data['subject'],
                'quantity' => (int) $data['quantity'],
            ]);

            Movement::create([
                'type' => 'entrada',
                'book_id' => $book->id,
                'funcionario_id' => $request->session()->get('employee.id'),
                'quantity' => (int) $data['quantity'],
                'justification' => $data['description'] ?: 'Cadastro inicial de novo material.',
            ]);
        });

        return redirect()
            ->route('senai.dashboard', ['view' => 'library'])
            ->with('status', "Novo material cadastrado: {$book->title}.");
    }

    public function withdrawBatch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'destination' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['nullable', 'integer', 'exists:books,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
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

        DB::transaction(function () use ($items, $data, $request): void {
            $totalsByBook = $items
                ->groupBy('book_id')
                ->map(fn (Collection $rows) => $rows->sum('quantity'));

            $books = Book::query()
                ->whereIn('id', $totalsByBook->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($totalsByBook as $bookId => $quantity) {
                $book = $books->get($bookId);

                if (!$book || $quantity > $book->quantity) {
                    $title = $book?->title ?? 'material selecionado';
                    $available = $book?->quantity ?? 0;

                    throw ValidationException::withMessages([
                        'items' => "Saldo insuficiente para {$title}. Solicitado: {$quantity}, disponivel: {$available}.",
                    ]);
                }
            }

            foreach ($totalsByBook as $bookId => $quantity) {
                $book = $books->get($bookId);
                $book->decrement('quantity', $quantity);

                Movement::create([
                    'type' => 'saida',
                    'book_id' => $book->id,
                    'funcionario_id' => $request->session()->get('employee.id'),
                    'quantity' => $quantity,
                    'justification' => 'Retirada em lote para ' . $data['destination'] . '.',
                ]);
            }
        });

        return redirect()
            ->route('senai.dashboard', ['view' => 'movements'])
            ->with('status', 'Retirada registrada com estoque validado.');
    }

    public function fulfillTeacherRequest(Request $request, int $teacherRequest): RedirectResponse
    {
        $teacherRequestModel = $this->findTeacherRequestModel($teacherRequest);
        $requestData = $this->findTeacherRequest($request, $teacherRequest);

        if (blank($requestData['bookId'] ?? null)) {
            throw ValidationException::withMessages([
                'teacher_request' => 'Este pedido ainda nao esta ligado a um livro do acervo.',
            ]);
        }

        DB::transaction(function () use ($teacherRequestModel, $requestData, $request): void {
            $book = Book::query()->lockForUpdate()->findOrFail($requestData['bookId']);

            if ($requestData['qty'] > $book->quantity) {
                throw ValidationException::withMessages([
                    'teacher_request' => "Saldo insuficiente para separar {$book->title}.",
                ]);
            }

            $book->decrement('quantity', $requestData['qty']);

            Movement::create([
                'type' => 'saida',
                'book_id' => $book->id,
                'funcionario_id' => $request->session()->get('employee.id'),
                'quantity' => $requestData['qty'],
                'justification' => 'Pedido do professor ' . $requestData['teacher'] . ' para ' . $requestData['turma'] . '.',
            ]);

            $teacherRequestModel?->update(['status' => 'atendido']);
        });

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

    public function storeTeacherRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:255'],
            'teacher_email' => ['nullable', 'email', 'max:255'],
            'class_name' => ['required', 'string', 'max:255'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $book = Book::findOrFail($data['book_id']);

        TeacherRequest::create([
            'teacher_name' => $data['teacher_name'],
            'teacher_email' => $data['teacher_email'] ?? null,
            'class_name' => $data['class_name'],
            'subject' => $book->subject,
            'book_id' => $book->id,
            'title' => $book->title,
            'quantity' => (int) $data['quantity'],
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

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
        $subject = $book->subject ?: 'Geral';
        $subjectMeta = [
            'Matematica' => ['author' => 'Equipe SENAI', 'publisher' => 'Editora SENAI-SP', 'pages' => 220],
            'Matemática' => ['author' => 'Equipe SENAI', 'publisher' => 'Editora SENAI-SP', 'pages' => 220],
            'Portugues' => ['author' => 'Equipe Linguagens', 'publisher' => 'Atica', 'pages' => 260],
            'Português' => ['author' => 'Equipe Linguagens', 'publisher' => 'Atica', 'pages' => 260],
            'Historia' => ['author' => 'Marcos Lima', 'publisher' => 'Moderna', 'pages' => 300],
            'História' => ['author' => 'Marcos Lima', 'publisher' => 'Moderna', 'pages' => 300],
            'Ciencias' => ['author' => 'Dra. Ana Ribeiro', 'publisher' => 'Saraiva', 'pages' => 280],
            'Ciências' => ['author' => 'Dra. Ana Ribeiro', 'publisher' => 'Saraiva', 'pages' => 280],
            'Tecnologia' => ['author' => 'Equipe Tech SENAI', 'publisher' => 'Novatec', 'pages' => 340],
            'Mecanica' => ['author' => 'Instrutores SENAI', 'publisher' => 'Editora SENAI-SP', 'pages' => 310],
            'Mecânica' => ['author' => 'Instrutores SENAI', 'publisher' => 'Editora SENAI-SP', 'pages' => 310],
            'Eletrica' => ['author' => 'Instrutores SENAI', 'publisher' => 'Editora SENAI-SP', 'pages' => 290],
            'Elétrica' => ['author' => 'Instrutores SENAI', 'publisher' => 'Editora SENAI-SP', 'pages' => 290],
        ];

        $meta = $subjectMeta[$subject] ?? [
            'author' => 'Curadoria SENAI',
            'publisher' => 'Biblioteca Tecnica',
            'pages' => 180 + ($book->id * 7),
        ];

        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $meta['author'],
            'publisher' => $meta['publisher'],
            'year' => (string) (2020 + ($book->id % 5)),
            'pages' => $meta['pages'],
            'isbn' => $book->isbn,
            'subject' => $subject,
            'quantity' => (int) $book->quantity,
            'desc' => 'Material didatico de ' . $subject . ' usado para aulas, reposicoes e retiradas controladas pelo almoxarifado.',
        ];
    }

    private function teacherRequestsFor(Collection $books, array $processedTeacherRequests = []): Collection
    {
        if (Schema::hasTable('teacher_requests') && TeacherRequest::query()->exists()) {
            return TeacherRequest::with('book')
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
            'teacher' => $teacherRequest->teacher_name,
            'email' => $teacherRequest->teacher_email,
            'turma' => $teacherRequest->class_name,
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

    private function ensureDemoOperationalData(): void
    {
        if (!Schema::hasTable('suppliers') || !Schema::hasTable('teacher_requests')) {
            return;
        }

        if (!Supplier::query()->exists()) {
            collect([
                ['name' => 'Editora SENAI-SP', 'contact_name' => 'Atendimento Corporativo', 'email' => 'pedidos@editorasenai.com.br', 'phone' => '(11) 3000-0101', 'lead_time_days' => 5],
                ['name' => 'Novatec Editora', 'contact_name' => 'Equipe Comercial', 'email' => 'comercial@novatec.com.br', 'phone' => '(11) 3214-4000', 'lead_time_days' => 9],
                ['name' => 'Editora Erica', 'contact_name' => 'Vendas Escolares', 'email' => 'escolas@erica.com.br', 'phone' => '(11) 3188-9000', 'lead_time_days' => 12],
            ])->each(fn ($supplier) => Supplier::create($supplier + ['status' => 'ativo']));
        }

        if (TeacherRequest::query()->exists() || !Book::query()->exists()) {
            return;
        }

        $books = Book::query()->orderBy('subject')->orderBy('title')->limit(4)->get();
        $teachers = [
            ['teacher_name' => 'Prof. Carlos Mendes', 'teacher_email' => 'carlos.mendes@escola.senai.br', 'class_name' => 'MEC-2A'],
            ['teacher_name' => 'Profa. Ana Paula', 'teacher_email' => 'ana.paula@escola.senai.br', 'class_name' => 'DS-1B'],
            ['teacher_name' => 'Prof. Roberto Alves', 'teacher_email' => 'roberto.alves@escola.senai.br', 'class_name' => 'ELE-3C'],
            ['teacher_name' => 'Profa. Fernanda Lima', 'teacher_email' => 'fernanda.lima@escola.senai.br', 'class_name' => 'ADM-1A'],
        ];

        $books->values()->each(function (Book $book, int $index) use ($teachers): void {
            $requestedQuantity = match ($index) {
                0 => max(1, min($book->quantity, 18)),
                1 => $book->quantity + 5,
                2 => max(1, min($book->quantity, 45)),
                default => $book->quantity + 10,
            };

            TeacherRequest::create($teachers[$index] + [
                'subject' => $book->subject,
                'book_id' => $book->id,
                'title' => $book->title,
                'quantity' => $requestedQuantity,
                'status' => $index === 2 ? 'atendido' : 'pendente',
                'due_date' => now()->addDays(3 + $index)->toDateString(),
                'notes' => 'Pedido inicial para demonstracao do fluxo do almoxarifado.',
            ]);
        });
    }

    private function nextOrderNumber(): string
    {
        return 'PED-' . now()->format('ymd-His') . '-' . Str::upper(Str::random(3));
    }
}
