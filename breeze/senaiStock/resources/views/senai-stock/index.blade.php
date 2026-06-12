<x-app-layout
    :active-view="$activeView"
    :navigation-items="$navigationItems"
    :employee="$employee"
    :purchase-cart-count="$purchaseCartCount"
    :withdraw-cart-count="$withdrawCartCount"
    :pending-teacher-requests="$pendingTeacherRequests"
    :alert-count="$alertCount"
    :supplier-count="$supplierCount"
    :search-books="$books"
>
    @php
        $booksArray = $books->values();
        $lowStockBooks = $booksArray->filter(fn ($book) => $book['quantity'] < $stockCriticalThreshold)->values();
        $booksBySubject = $booksArray->groupBy('subject')->map(fn ($group) => $group->sum('quantity'))->sortDesc();
        $turmaOptions = $turmas->map(fn ($turma) => [
            'id' => $turma->id,
            'nome_turma' => $turma->nome_turma,
            'curso_id' => $turma->curso_id,
            'curso_nome' => $turma->curso?->nome_curso,
        ])->values();

        $monthNames = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];

        $groupedOrders = $purchaseOrders
            ->sortByDesc(function ($order) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $order['date']);
            })
            ->groupBy(function ($order) use ($monthNames) {
                $date = \Carbon\Carbon::createFromFormat('d/m/Y', $order['date']);
                return $monthNames[(int) $date->format('n')] . ' de ' . $date->format('Y');
            });

        $pendingRequests = $teacherRequests
            ->whereIn('status', ['pendente', 'aprovado', 'compra', 'compra_aprovada', 'separado_parcial'])
            ->sortBy(fn ($item) => $item['dueDateSort'] ?? '9999-12-31 23:59:59')
            ->values();
        $processedRequests = $teacherRequests->whereNotIn('status', ['pendente', 'aprovado', 'compra', 'compra_aprovada', 'separado_parcial']);
        $pendingApprovals = $purchaseOrders
            ->whereIn('status', ['pendente_aprovacao', 'aguardando'])
            ->filter(fn ($order) => !empty($order['id']));
        $can = fn (string $ability) => $permissions[$ability] ?? false;
        $canView = fn (string $view) => collect($navigationItems)->contains(fn ($item) => ($item['id'] ?? null) === $view);
        $isAdmin = $employeeRole === \App\Support\EmployeeRole::COORDENADOR;
        $defaultSupplier = $suppliers->first();
        $teacherRequestFormHasErrors = $errors->hasAny(['turma_id', 'curso_id', 'book_id', 'quantity', 'due_date', 'notes']);
        $greeting = 'Boa noite';
        $hour = now()->hour;
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Bom dia';
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = 'Boa tarde';
        }
    @endphp

    @if ($activeView === 'insights')
        <div class="animate-in fade-in duration-500 max-w-4xl mx-auto pt-4 md:pt-10">
            <div class="text-center mb-10">
                <h1 class="text-4xl md:text-5xl font-semibold tracking-tight text-gray-900 mb-4">{{ $greeting }}, {{ data_get($employee, 'name', 'Coordenação') }}.</h1>
                <p class="text-lg text-gray-500 font-medium">Acompanhe o estoque e as atividades mais importantes.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                @if ($canView('reports'))
                <a href="{{ route('senai.dashboard', ['view' => 'reports']) }}" class="senai-surface-hover bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:border-red-100 hover:bg-red-50/30 transition">
                    <p class="text-sm text-gray-500 font-medium mb-1">Estoque crítico</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $lowStockBooks->count() }} títulos</p>
                    <p class="text-sm text-gray-400 mt-1">abaixo de {{ $stockCriticalThreshold }} unidades</p>
                </a>
                @endif
                @if ($canView('library'))
                <a href="{{ route('senai.dashboard', ['view' => 'library']) }}" class="senai-surface-hover bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:border-gray-200 hover:bg-gray-50 transition">
                    <p class="text-sm text-gray-500 font-medium mb-1">Exemplares no acervo</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $totalQuantity }}</p>
                    <p class="text-sm text-gray-400 mt-1">livros disponíveis</p>
                </a>
                @endif
                @if ($canView('teacher_requests'))
                <a href="{{ route('senai.dashboard', ['view' => 'teacher_requests']) }}" class="senai-surface-hover bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:border-amber-100 hover:bg-amber-50/30 transition">
                    <p class="text-sm text-gray-500 font-medium mb-1">Pedidos pendentes</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $pendingTeacherRequests }}</p>
                    <p class="text-sm text-gray-400 mt-1">aguardando separação</p>
                </a>
                @endif
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">Atualizações e Tarefas</h2>
                    <a href="{{ route('senai.dashboard', ['view' => 'teacher_requests']) }}" class="text-sm font-medium text-red-600 hover:text-red-700">Ver todas</a>
                </div>
                <div class="space-y-4">
                    @forelse ($teacherRequests->take(4) as $request)
                        <div class="flex items-start gap-4 rounded-2xl border border-gray-100 p-4 hover:bg-gray-50/70 transition-colors">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 {{ $request['status'] === 'atendido' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                ⌛
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between gap-4 mb-1">
                                    <p class="font-semibold text-gray-900">{{ $request['teacher'] }} solicitou {{ $request['qty'] }}x {{ $request['title'] }}</p>
                                    <span class="text-xs text-gray-400 font-medium whitespace-nowrap">{{ $request['date'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600">Turma de <span class="font-medium text-gray-900">{{ $request['subject'] }}</span></p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">Nenhuma atualização recente.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-8 bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Alertas e ações</h2>
                        <p class="mt-1 text-sm text-gray-500">Pendências operacionais reunidas na página inicial.</p>
                    </div>
                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">{{ $alertCount }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($alerts->take(6) as $alert)
                        <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 p-4 sm:flex-row sm:items-center">
                            <div class="flex-1">
                                <span class="text-[11px] font-bold uppercase {{ $alert['severity'] === 'critical' ? 'text-red-700' : ($alert['severity'] === 'warning' ? 'text-amber-700' : 'text-gray-500') }}">
                                    {{ $alert['type'] === 'stock' ? 'Estoque' : ($alert['type'] === 'request' ? 'Pedido' : 'Compra') }}
                                </span>
                                <p class="font-semibold text-gray-900">{{ $alert['title'] }}</p>
                                <p class="text-sm text-gray-500">{{ $alert['message'] }}</p>
                            </div>
                            @if ($alert['type'] === 'stock' && $can('alerts.purchase'))
                                <form method="POST" action="{{ route('stock.alerts.purchase', $alert['bookId']) }}">@csrf<button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">{{ $alert['action'] }}</button></form>
                            @elseif ($alert['type'] === 'purchase' && $canView('purchases'))
                                <a href="{{ route('senai.dashboard', ['view' => 'purchases']) }}" class="rounded-xl border border-gray-200 px-4 py-2 text-center text-sm font-semibold text-gray-700">{{ $alert['action'] }}</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Sem alertas no momento.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @elseif ($activeView === 'reports')
        <div class="animate-in fade-in duration-500">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Relatórios</h1>
                <p class="text-gray-500 mt-1 text-base">Resumo do acervo, análises por área e lista completa de livros.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 font-medium mb-1">Títulos</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $booksArray->count() }}</p>
                </div>
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 font-medium mb-1">Exemplares</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $totalQuantity }}</p>
                </div>
                <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100">
                    <p class="text-xs text-emerald-700 font-medium mb-1">Estoque adequado</p>
                    <p class="text-3xl font-semibold text-emerald-700">{{ $booksArray->where('quantity', '>=', $stockCriticalThreshold)->count() }}</p>
                </div>
                <div class="bg-red-50 rounded-2xl p-5 border border-red-100">
                    <p class="text-xs text-red-700 font-medium mb-1">Estoque crítico</p>
                    <p class="text-3xl font-semibold text-red-700">{{ $lowStockBooks->count() }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 mb-5">Volume por área</h2>
                    <div class="space-y-3">
                        @foreach ($booksBySubject as $subject => $qty)
                            <div class="flex items-center text-sm gap-3">
                                <span class="w-28 text-gray-500 font-medium truncate">{{ $subject }}</span>
                                <div class="flex-1 bg-gray-50 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-gray-900 h-full rounded-full" style="width: {{ max(($qty / max($booksBySubject->max(), 1)) * 100, 15) }}%"></div>
                                </div>
                                <span class="w-10 text-right font-semibold text-gray-900">{{ $qty }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Baixo estoque</h2>
                        <span class="text-xs text-gray-400">abaixo de {{ $stockCriticalThreshold }} un</span>
                    </div>
                    <div class="overflow-x-auto max-h-72">
                        <table class="w-full text-left text-sm" data-table-skip>
                            <thead class="bg-gray-50/80 text-gray-500 sticky top-0">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Livro</th>
                                    <th class="px-5 py-3 font-medium text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockBooks as $book)
                                    <tr class="border-t border-gray-50">
                                        <td class="px-5 py-3">
                                            <p class="font-medium text-gray-900 truncate max-w-[220px]">{{ $book['title'] }}</p>
                                            <p class="text-xs text-gray-400">{{ $book['subject'] }}</p>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <span class="inline-flex px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">{{ $book['quantity'] }} un</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-5 py-8 text-center text-gray-500">Nenhum item crítico.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm" x-data="reportBooksTable(@js($booksArray), {{ $stockCriticalThreshold }})" data-native-table-filters>
                <div class="px-6 py-5 border-b border-gray-100 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Lista completa de livros</h2>
                        <span class="text-sm text-gray-400" x-text="`${rows.length} registro(s)`"></span>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <input type="search" x-model="query" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none sm:w-72" placeholder="Buscar pelo nome do livro">
                        <select x-model="subject" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none sm:w-56">
                            <option value="">Todas as áreas</option>
                            <template x-for="area in subjects" :key="area">
                                <option :value="area" x-text="area"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-5 py-3.5 font-medium"><button type="button" @click="sort('title')">Título <span x-text="indicator('title')"></span></button></th>
                                <th class="px-5 py-3.5 font-medium"><button type="button" @click="sort('subject')">Área <span x-text="indicator('subject')"></span></button></th>
                                <th class="px-5 py-3.5 font-medium"><button type="button" @click="sort('isbn')">ISBN <span x-text="indicator('isbn')"></span></button></th>
                                <th class="px-5 py-3.5 font-medium text-right"><button type="button" @click="sort('quantity')">Qtd. <span x-text="indicator('quantity')"></span></button></th>
                                <th class="px-5 py-3.5 font-medium text-center"><button type="button" @click="sort('status')">Status <span x-text="indicator('status')"></span></button></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="book in rows" :key="book.id">
                                <tr class="border-t border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-5 py-3 font-medium text-gray-900 max-w-[280px] truncate" x-text="book.title"></td>
                                    <td class="px-5 py-3 text-gray-600" x-text="book.subject"></td>
                                    <td class="px-5 py-3 text-gray-500 font-mono text-xs" x-text="book.isbn"></td>
                                    <td class="px-5 py-3 text-right font-semibold" x-text="book.quantity"></td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold" :class="Number(book.quantity) < threshold ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'" x-text="status(book)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'teacher_requests')
        <div class="animate-in fade-in duration-500">
            <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900">{{ $employeeRole === 'Professor' ? 'Meus Pedidos' : 'Pedidos' }}</h1>
                <p class="text-gray-500 mt-1 text-base">
                    @if ($employeeRole === 'Professor')
                        Solicite material didático para suas turmas.
                    @else
                        Solicitações ligadas ao acervo, separação manual e aprovação de compras.
                    @endif
                </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($employeeRole === 'Coordenador')
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 border border-amber-100 px-3 py-1.5 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Coordenador - acesso total
                        </span>
                    @endif
                </div>
            </div>

            @if ($employeeRole === 'Professor' && $professorNotifications->isNotEmpty())
                <div class="mb-8 rounded-3xl border border-blue-100 bg-blue-50/60 p-6">
                    <h2 class="text-lg font-semibold text-blue-950">Atualizações dos seus pedidos</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($professorNotifications as $notification)
                            <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm transition hover:border-blue-200">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-semibold text-gray-900">{{ $notification['title'] }}</p>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-medium text-gray-400">{{ $notification['date'] }}</span>
                                        <form method="POST" action="{{ route('stock.teacher-requests.notifications.dismiss', $notification['requestId']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-blue-700 hover:text-red-700">Dispensar</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $notification['message'] }}</p>
                                <p class="mt-2 text-xs text-gray-500">Turma: {{ $notification['className'] ?? 'Não informada' }} · Curso: {{ $notification['courseName'] ?? 'Não informado' }}</p>
                                <p class="mt-2 text-xs font-bold uppercase {{ ($notification['status'] ?? null) === 'rejeitado' ? 'text-red-700' : 'text-blue-700' }}">{{ $notification['status'] ?: 'Atualização' }} · {{ $notification['protocol'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($can('teacher_requests.create'))
            <details class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-8" @if ($initialRequestBookId || $teacherRequestFormHasErrors || old('turma_id') || old('due_date')) open @endif>
                <summary class="cursor-pointer list-none flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $employeeRole === 'Professor' ? 'Novo pedido' : 'Registrar pedido manualmente' }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            @if ($employeeRole === 'Professor')
                                Preencha os dados do material que você precisa.
                            @else
                                Registre pedidos em nome de professores.
                            @endif
                        </p>
                    </div>
                    <span class="text-sm font-semibold text-red-600">Novo pedido</span>
                </summary>
                <form method="POST" action="{{ route('stock.teacher-requests.store') }}" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5" x-data="teacherRequestForm(@js($turmaOptions), @js($booksArray), @js($initialRequestBookId), @js(['turmaId' => old('turma_id'), 'cursoId' => old('curso_id'), 'bookId' => old('book_id')]))">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-900 mb-2">Solicitante</label>
                        <input type="text" value="{{ data_get($employee, 'name', 'Usuário atual') }}" readonly class="w-full rounded-xl border border-gray-100 bg-gray-100 px-4 py-3.5 text-sm font-semibold text-gray-700 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Turma</label>
                        <select name="turma_id" x-model="turmaId" @change="selectTurma()" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                            <option value="">Selecione uma turma...</option>
                            <template x-for="turma in filteredTurmas" :key="turma.id">
                                <option :value="turma.id" x-text="turma.nome_turma"></option>
                            </template>
                        </select>
                    </div>
                    @if (! $initialRequestBookId)
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Curso</label>
                        <select name="curso_id" x-model="cursoId" required disabled class="w-full rounded-xl border border-gray-100 bg-gray-100 px-4 py-3.5 text-sm text-gray-600 outline-none">
                            <option value="">Selecione um curso...</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id }}">{{ $curso->nome_curso }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="curso_id" :value="cursoId">
                    </div>
                    @else
                        <input type="hidden" name="curso_id" :value="cursoId">
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Prazo desejado</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" min="{{ now()->toDateString() }}" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3.5 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-900 mb-2">Livro</label>
                        @if ($initialRequestBookId)
                            @php $initialBook = $booksArray->firstWhere('id', $initialRequestBookId); @endphp
                            <input type="text" value="{{ $initialBook['title'] ?? 'Livro selecionado' }}" readonly class="w-full rounded-xl border border-gray-100 bg-gray-100 px-4 py-3.5 text-sm font-semibold text-gray-700 outline-none">
                            <input type="hidden" name="book_id" value="{{ $initialRequestBookId }}">
                        @else
                        <select @if (! $initialRequestBookId) name="book_id" @endif x-model="bookId" required :disabled="@js((bool) $initialRequestBookId) || !turmaId" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm focus:ring-2 focus:ring-red-500 outline-none disabled:opacity-70">
                            <option value="" x-text="turmaId ? 'Selecione um livro do curso...' : 'Selecione uma turma primeiro...'"></option>
                            <template x-for="book in filteredBooks" :key="book.id">
                                <option :value="book.id" x-text="`${book.title} (${book.quantity} un)`"></option>
                            </template>
                        </select>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Quantidade</label>
                        <input type="number" name="quantity" value="{{ old('quantity') }}" min="1" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: 20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Observações</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: aula prática de quarta-feira">
                    </div>
                    <div class="md:col-span-2 flex flex-col gap-3 sm:flex-row">
                        <button type="submit" class="flex-1 rounded-xl bg-gray-900 px-4 py-3.5 text-sm font-semibold text-white hover:bg-gray-800">Adicionar a fila</button>
                        <button type="button" @click="clearForm($el.closest('form'))" class="rounded-xl border border-gray-200 bg-white px-5 py-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpar campos</button>
                    </div>
                </form>
            </details>
            @endif

            @if ($employeeRole !== 'Professor')
            <div class="space-y-4 mb-12">
                @forelse ($pendingRequests as $request)
                    <div class="bg-white rounded-[20px] border border-gray-200/80 shadow-sm overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            <div class="p-5 lg:col-span-3 border-b lg:border-b-0 lg:border-r border-gray-100 bg-gray-50/30">
                                <p class="font-semibold text-gray-900">{{ $request['teacher'] }}</p>
                                <p class="mt-2 text-xs font-semibold text-gray-700">Turma: {{ $request['turma'] ?? 'Não informada' }}</p>
                                <p class="mt-1 text-xs text-gray-500">Curso: {{ $request['course'] ?? $request['subject'] ?? 'Não informado' }}</p>
                                @if (!empty($request['dueDate']))
                                    <p class="mt-1 text-xs font-medium text-red-600">Prazo: {{ $request['dueDate'] }}</p>
                                @endif
                                <p class="text-[11px] text-gray-400 mt-4">{{ $request['protocol'] ?? '#' . $request['id'] }} - {{ $request['date'] }} às {{ $request['time'] }}</p>
                            </div>
                            <div class="p-5 lg:col-span-6">
                                <h3 class="font-semibold text-gray-900 text-lg mb-2">{{ $request['title'] }}</h3>
                                @php
                                    $requestMissing = (int) ($request['missing'] ?? 0);
                                    $requestAvailable = (int) ($request['available'] ?? 0);
                                @endphp
                                <p class="text-sm text-gray-600">Quantidade solicitada: <span class="font-semibold text-gray-900">{{ $request['qty'] }} un</span></p>
                                @if (!empty($request['notes']) || !empty($request['lastMessage']))
                                    <p class="mt-3 text-sm text-gray-500">{{ $request['lastMessage'] ?? $request['notes'] }}</p>
                                @endif
                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    @php
                                        $activeStatusLabel = match ($request['status']) {
                                            'aprovado' => 'Aprovado',
                                            'compra_aprovada' => 'Compra aprovada',
                                            'compra' => 'Em compra',
                                            'separado_parcial' => 'Retirada parcial',
                                            default => ucfirst($request['status']),
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase bg-gray-100 text-gray-600 border border-gray-200">
                                        {{ $activeStatusLabel }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase {{ in_array($request['status'], ['compra', 'compra_aprovada'], true) ? 'bg-violet-50 text-violet-700 border border-violet-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ in_array($request['status'], ['compra', 'compra_aprovada'], true) ? 'Pedido de compra' : 'Pedido de separação' }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $requestMissing === 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                        {{ $requestMissing === 0 ? 'Disponivel' : 'Faltam ' . $requestMissing }}
                                    </span>
                                    <span class="text-xs font-medium text-gray-500">Saldo atual: {{ $requestAvailable }} un</span>
                                </div>
                            </div>
                            <div class="p-5 lg:col-span-3 flex flex-col gap-3 justify-center bg-gray-50/40">
                                @if ($requestMissing === 0 && in_array($request['status'], ['aprovado', 'separado_parcial'], true) && $can('teacher_requests.fulfill'))
                                    <a href="{{ route('senai.dashboard', ['view' => 'stock', 'tab' => 'saida']) }}" class="w-full text-center text-sm font-medium bg-emerald-600 text-white px-4 py-2.5 rounded-xl hover:bg-emerald-700">Registrar retirada</a>
                                @elseif (($request['status'] ?? null) === 'pendente' && $requestMissing === 0)
                                    <span class="w-full text-center text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100 px-4 py-2.5 rounded-xl">Aprove antes de separar</span>
                                @elseif (($request['status'] ?? null) === 'compra')
                                    <span class="w-full text-center text-sm font-medium bg-amber-50 text-amber-700 border border-amber-100 px-4 py-2.5 rounded-xl">Compra aguardando aprovacao e recebimento</span>
                                @elseif (($request['status'] ?? null) === 'compra_aprovada')
                                    <span class="w-full text-center text-sm font-medium bg-cyan-50 text-cyan-700 border border-cyan-100 px-4 py-2.5 rounded-xl">Compra aprovada, aguardando recebimento</span>
                                @endif
                                @if ($isAdmin && ($request['status'] ?? null) === 'compra' && !empty($request['purchaseOrderId']))
                                    <form method="POST" action="{{ route('stock.purchases.approve', $request['purchaseOrderId']) }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Aprovar compra agora</button>
                                    </form>
                                    <a href="{{ route('senai.dashboard', ['view' => 'purchases', 'tab' => 'aprovacoes']) }}" class="w-full rounded-xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-center text-sm font-semibold text-blue-700 hover:bg-blue-100">Abrir aprovações</a>
                                @endif
                                @if ($isAdmin && ($request['status'] ?? null) === 'pendente')
                                    <form method="POST" action="{{ route('stock.teacher-requests.approve', $request['id']) }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Aprovar pedido</button>
                                    </form>
                                @endif
                                @if ($isAdmin)
                                <details class="rounded-xl border border-red-100 bg-red-50 p-3">
                                    <summary class="cursor-pointer text-center text-xs font-bold uppercase tracking-wide text-red-700">Rejeitar com observação</summary>
                                    <form method="POST" action="{{ route('stock.teacher-requests.reject', $request['id']) }}" class="mt-3 space-y-2">
                                        @csrf
                                        <textarea name="message" rows="3" required class="w-full rounded-xl border border-red-100 bg-white px-3 py-2 text-xs focus:ring-2 focus:ring-red-500 outline-none" placeholder="Explique ao professor por que o pedido ou compra foi rejeitado"></textarea>
                                        <button type="submit" class="w-full rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Confirmar rejeição</button>
                                    </form>
                                </details>
                                @endif
                                @if (($request['persisted'] ?? false) && ($isAdmin || ((int) ($request['requestedBy'] ?? 0) === (int) data_get($employee, 'id') && ($request['status'] ?? null) === 'pendente')))
                                    <form method="POST" action="{{ route('stock.teacher-requests.destroy', $request['id']) }}" onsubmit="return confirm('Excluir este pedido?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">Excluir pedido</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="senai-empty">Nenhum pedido pendente no momento.</div>
                @endforelse
            </div>
            @endif

            @php
                $historyRequests = $employeeRole === 'Professor' ? $teacherRequests : $processedRequests;
            @endphp
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $employeeRole === 'Professor' ? 'Meus Pedidos' : 'Histórico de Pedidos Processados' }}</h2>
                    <span class="text-sm text-gray-400">{{ $historyRequests->count() }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table
                        class="w-full text-left border-collapse text-sm whitespace-nowrap"
                        data-table-search-placeholder="Buscar por livro ou solicitante"
                        data-table-filter-column="{{ $employeeRole === 'Professor' ? 2 : 3 }}"
                        data-table-filter-label="Todos os status"
                    >
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                @if ($employeeRole !== 'Professor')
                                    <th class="px-6 py-4 font-medium border-r border-gray-100">Professor / Solicitante</th>
                                @endif
                                <th class="px-6 py-4 font-medium border-r border-gray-100">Título Solicitado</th>
                                <th class="px-6 py-4 font-medium border-r border-gray-100 text-center w-24">Qtd.</th>
                                <th class="px-6 py-4 font-medium border-r border-gray-100 text-center w-36">Status</th>
                                <th class="px-6 py-4 font-medium w-36">Data</th>
                                <th class="px-6 py-4 font-medium text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historyRequests as $request)
                                <tr class="border-t border-gray-50 hover:bg-gray-50/50">
                                    @if ($employeeRole !== 'Professor')
                                        <td class="px-6 py-4 border-r border-gray-50 font-medium text-gray-900">{{ $request['teacher'] }} <span class="text-xs text-gray-400 font-normal ml-1">({{ $request['subject'] }})</span></td>
                                    @endif
                                    <td class="px-6 py-4 border-r border-gray-50 text-gray-700">
                                        <p class="font-medium text-gray-900">{{ $request['title'] }}</p>
                                        @if ($employeeRole === 'Professor' && ($request['status'] ?? null) === 'rejeitado')
                                            <details class="group mt-3 max-w-xl whitespace-normal rounded-xl border border-red-100 bg-red-50">
                                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2.5 text-[10px] font-bold uppercase tracking-wide text-red-700">
                                                    Motivo da rejeição
                                                    <span class="transition group-open:rotate-180">⌄</span>
                                                </summary>
                                                <p class="border-t border-red-100 px-3 py-2.5 text-xs leading-relaxed text-red-800">{{ $request['rejectionReason'] ?? $request['lastMessage'] ?? 'Motivo não informado.' }}</p>
                                            </details>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 border-r border-gray-50 text-center font-semibold text-gray-700">{{ $request['qty'] }}</td>
                                    <td class="px-6 py-4 border-r border-gray-50 text-center">
                                        @php
                                            $statusLabel = match ($request['status']) {
                                                'atendido' => ['Atendido', 'bg-emerald-50 text-emerald-600 border-emerald-200'],
                                                'separado_parcial' => ['Retirada parcial', 'bg-blue-50 text-blue-600 border-blue-200'],
                                                'aprovado' => ['Aprovado', 'bg-blue-50 text-blue-600 border-blue-200'],
                                                'compra_aprovada' => ['Compra aprovada', 'bg-cyan-50 text-cyan-700 border-cyan-200'],
                                                'compra' => ['Em compra', 'bg-amber-50 text-amber-600 border-amber-200'],
                                                'rejeitado' => ['Rejeitado', 'bg-red-50 text-red-700 border-red-200'],
                                                default => ['Pendente', 'bg-gray-50 text-gray-600 border-gray-200'],
                                            };
                                        @endphp
                                        <span class="text-[10px] uppercase font-bold px-2 py-1 rounded border {{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $request['date'] }} {{ $request['time'] }}</td>
                                    <td class="px-6 py-4 text-right">
                                        @if (($request['persisted'] ?? false) && ($isAdmin || ((int) ($request['requestedBy'] ?? 0) === (int) data_get($employee, 'id') && ($request['status'] ?? null) === 'pendente')))
                                            <form method="POST" action="{{ route('stock.teacher-requests.destroy', $request['id']) }}" onsubmit="return confirm('Excluir este pedido?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-700 hover:text-red-900">Excluir pedido</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $employeeRole === 'Professor' ? 5 : 6 }}" class="senai-empty-cell">Nenhum pedido encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'purchases')
        <div class="animate-in fade-in duration-500" x-data="preservedTabs(@js($activeTab ?? ($can('purchases.approve') ? 'aprovacoes' : ($can('purchases.create') ? 'nova' : 'historico'))))">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Compras</h1>
                <p class="text-gray-500 mt-1 text-base">Monte pedidos de reposição e consulte o histórico de compras.</p>
            </div>

            <div class="flex p-1 bg-gray-100 rounded-xl mb-8 w-full sm:w-fit">
                @if ($can('purchases.create'))
                <button type="button" @click="selectTab('nova')" class="px-5 py-2.5 text-sm font-medium rounded-lg" :class="tab === 'nova' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Nova compra</button>
                @endif
                @if ($can('purchases.approve'))
                <button type="button" @click="selectTab('aprovacoes')" class="px-5 py-2.5 text-sm font-medium rounded-lg" :class="tab === 'aprovacoes' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Aprovações ({{ $pendingApprovals->count() }})</button>
                @endif
                <button type="button" @click="selectTab('historico')" class="px-5 py-2.5 text-sm font-medium rounded-lg" :class="tab === 'historico' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Histórico</button>
            </div>

            @if ($can('purchases.create'))
            <div x-show="tab === 'nova'" x-cloak x-data="purchaseRequestForm(@js($booksArray), @js(old('book_id', request('book_id'))), @js(old('course_filter')))">
                <form method="POST" action="{{ route('stock.purchases.generate') }}" class="mx-auto max-w-3xl rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
                    @csrf
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Registrar pedido de reposição</h2>
                        <p class="mt-1 text-sm text-gray-500">Cada pedido de compra contém um livro para facilitar a aprovação e o recebimento.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900">Curso</label>
                            <select name="course_filter" x-model="course" @change="bookId = ''" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                                <option value="">Todos os cursos</option>
                                <template x-for="subject in subjects" :key="subject">
                                    <option :value="subject" x-text="subject"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900">Livro</label>
                            <select name="book_id" x-model="bookId" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                                <option value="">Selecione um livro...</option>
                                <template x-for="book in filteredBooks" :key="book.id">
                                    <option :value="book.id" x-text="`${book.title} (estoque: ${book.quantity})`"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900">Quantidade</label>
                            <input type="number" name="quantity" value="{{ old('quantity', request('quantity', 1)) }}" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-900">Justificativa</label>
                            <input type="text" name="justification" value="{{ old('justification', request('justification')) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: reposição para o próximo semestre">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-900">Observação interna</label>
                            <textarea name="notes" rows="2" class="w-full resize-none rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 outline-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="mt-6 w-full rounded-xl bg-gray-900 px-4 py-3.5 text-sm font-semibold text-white hover:bg-gray-800">Registrar compra</button>
                </form>
            </div>

            @endif

            @if ($can('purchases.approve'))
            <div x-show="tab === 'aprovacoes'" x-cloak class="space-y-4">
                @forelse ($pendingApprovals as $order)
                    <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $order['orderId'] }}</p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ collect($order['items'])->sum(fn ($item) => $item['requestedQty'] ?? $item['quantity'] ?? 0) }} exemplar(es)
                                    solicitados em {{ $order['date'] }} às {{ $order['time'] }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">Solicitado por {{ $order['requestedBy'] ?? 'não informado' }}@if (!empty($order['supplier'])) · Fornecedor: {{ $order['supplier'] }} @endif</p>
                            </div>
                            <form method="POST" action="{{ route('stock.purchases.approve', $order['id']) }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">Aprovar compra</button>
                            </form>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($order['items'] as $item)
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $item['title'] }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $item['subject'] ?? 'Área não informada' }}@if (!empty($item['isbn'])) · ISBN {{ $item['isbn'] }} @endif</p>
                                        </div>
                                        <span class="shrink-0 rounded-lg bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">{{ $item['requestedQty'] ?? $item['quantity'] ?? 0 }} un.</span>
                                    </div>
                                    <p class="mt-3 text-sm text-gray-600">{{ $item['justification'] ?? 'Pedido de compra.' }}</p>
                                    @if (!empty($item['teacherRequest']))
                                        <div class="mt-3 border-t border-gray-200 pt-3 text-xs text-gray-500">
                                            <p class="font-semibold text-gray-700">Solicitação {{ $item['teacherRequest'] }}</p>
                                            <p>{{ $item['teacherName'] ?? 'Professor não informado' }}@if (!empty($item['className'])) · {{ $item['className'] }} @endif @if (!empty($item['courseName'])) · {{ $item['courseName'] }} @endif</p>
                                            @if (!empty($item['dueDate'])) <p>Necessário até {{ $item['dueDate'] }}</p> @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if (!empty($order['notes']))
                            <p class="mt-4 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-800"><span class="font-semibold">Observação interna:</span> {{ $order['notes'] }}</p>
                        @endif
                    </div>
                @empty
                    <div class="senai-empty">Nenhuma compra aguardando aprovação.</div>
                @endforelse
            </div>
            @endif

            <div x-show="tab === 'historico'" x-cloak>
            <div class="space-y-5">
                @forelse ($groupedOrders as $monthYear => $orders)
                    <details class="bg-white border border-gray-200 shadow-sm rounded-2xl overflow-hidden" @if ($loop->first) open @endif>
                        <summary class="cursor-pointer list-none p-5 flex items-center justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 text-lg">{{ $monthYear }}</h3>
                                <p class="text-sm text-gray-500 font-medium">{{ count($orders) }} pedido(s) registrados</p>
                            </div>
                            <span class="text-gray-400">⌄</span>
                        </summary>
                        <div class="border-t border-gray-100 p-4 space-y-4">
                            @foreach ($orders as $order)
                                <details class="group bg-gray-50 rounded-2xl border border-gray-100">
                                    <summary class="cursor-pointer list-none p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $order['orderId'] }}</p>
                                                <p class="mt-1 text-sm font-medium text-gray-700">{{ collect($order['items'])->pluck('title')->filter()->join(', ') }}</p>
                                                <p class="text-xs text-gray-500">Em {{ $order['date'] }} às {{ $order['time'] }} · Clique para ver os detalhes</p>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                @php
                                                    $purchaseStatus = match ($order['status'] ?? 'pendente_aprovacao') {
                                                        'entregue' => ['Entregue', 'bg-emerald-50 text-emerald-700 border border-emerald-100'],
                                                        'recebimento_parcial' => ['Recebimento parcial', 'bg-cyan-50 text-cyan-700 border border-cyan-100'],
                                                        'aprovado' => ['Aprovado', 'bg-blue-50 text-blue-700 border border-blue-100'],
                                                        'aguardando' => ['Aguardando aprovacao', 'bg-amber-50 text-amber-700 border border-amber-100'],
                                                        default => ['Aguardando aprovacao', 'bg-amber-50 text-amber-700 border border-amber-100'],
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $purchaseStatus[1] }}">{{ $purchaseStatus[0] }}</span>
                                                <span class="text-gray-400 transition group-open:rotate-180">⌄</span>
                                            </div>
                                        </div>
                                    </summary>
                                    <div class="border-t border-gray-200 bg-white p-4">
                                        <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                            <div class="rounded-xl bg-gray-50 p-3"><p class="text-xs font-medium text-gray-500">Quantidade solicitada</p><p class="mt-1 text-lg font-semibold text-gray-900">{{ collect($order['items'])->sum(fn ($item) => $item['requestedQty'] ?? $item['quantity'] ?? 0) }}</p></div>
                                            <div class="rounded-xl bg-gray-50 p-3"><p class="text-xs font-medium text-gray-500">Quantidade recebida</p><p class="mt-1 text-lg font-semibold text-gray-900">{{ collect($order['items'])->sum(fn ($item) => $item['receivedQty'] ?? 0) }}</p></div>
                                            <div class="rounded-xl bg-gray-50 p-3"><p class="text-xs font-medium text-gray-500">Quantidade pendente</p><p class="mt-1 text-lg font-semibold text-gray-900">{{ collect($order['items'])->sum(fn ($item) => $item['remainingQty'] ?? $item['requestedQty'] ?? $item['quantity'] ?? 0) }}</p></div>
                                            <div class="rounded-xl bg-gray-50 p-3"><p class="text-xs font-medium text-gray-500">Solicitado por</p><p class="mt-1 text-sm font-semibold text-gray-900">{{ $order['requestedBy'] ?? 'Não informado' }}</p></div>
                                        </div>
                                        <div class="mb-4 flex flex-wrap gap-2">
                                            @if (in_array(($order['status'] ?? ''), ['aprovado', 'recebimento_parcial'], true) && !empty($order['id']) && $can('purchases.deliver'))
                                                <a href="{{ route('senai.dashboard', ['view' => 'stock', 'tab' => 'entrada']) }}" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Registrar recebimento</a>
                                            @endif
                                            <button type="button" onclick="window.print()" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white">Imprimir</button>
                                        </div>
                                        @if (!empty($order['notes']))
                                            <p class="mb-4 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-800"><span class="font-semibold">Observação interna:</span> {{ $order['notes'] }}</p>
                                        @endif
                                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                                            <table class="w-full text-left text-sm" data-table-skip>
                                                <thead class="bg-gray-50 text-gray-500">
                                                    <tr>
                                                        <th class="px-4 py-3 font-medium">Material</th>
                                                        <th class="px-4 py-3 font-medium">Solicitação</th>
                                                        <th class="px-4 py-3 font-medium text-right">Solicitado</th>
                                                        <th class="px-4 py-3 font-medium text-right">Recebido</th>
                                                        <th class="px-4 py-3 font-medium text-right">Pendente</th>
                                                        <th class="px-4 py-3 font-medium">Justificativa</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($order['items'] as $item)
                                                        <tr class="border-t border-gray-50">
                                                            <td class="px-4 py-3"><p class="font-medium text-gray-900">{{ $item['title'] }}</p><p class="text-xs text-gray-500">{{ $item['subject'] ?? 'Área não informada' }}@if (!empty($item['isbn'])) · ISBN {{ $item['isbn'] }} @endif</p></td>
                                                            <td class="px-4 py-3 text-gray-500">@if (!empty($item['teacherRequest']))<p class="font-medium text-gray-700">{{ $item['teacherRequest'] }}</p><p class="text-xs">{{ $item['teacherName'] ?? 'Professor não informado' }}@if (!empty($item['className'])) · {{ $item['className'] }} @endif</p>@else Reposição de estoque @endif</td>
                                                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $item['requestedQty'] ?? $item['quantity'] ?? 0 }}</td>
                                                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $item['receivedQty'] ?? 0 }}</td>
                                                            <td class="px-4 py-3 text-right font-semibold text-amber-700">{{ $item['remainingQty'] ?? $item['requestedQty'] ?? $item['quantity'] ?? 0 }}</td>
                                                            <td class="px-4 py-3 text-gray-500">{{ $item['justification'] ?? 'Pedido de compra.' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </details>
                @empty
                    <div class="senai-empty">Nenhuma planilha de compra registrada no sistema.</div>
                @endforelse
            </div>
            </div>
        </div>
    @elseif ($activeView === 'book_registration')
        <div class="animate-in fade-in duration-500 max-w-4xl mx-auto">
            <div class="senai-school-hero">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Escola · Acervo</p>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Livro</h1>
                <p class="text-gray-500 mt-1 text-base">Inclua um novo título no catálogo e registre seu estoque inicial.</p>
                <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold text-gray-600">
                    <span class="rounded-full bg-gray-100 px-3 py-1.5">{{ $booksArray->count() }} títulos</span>
                    <span class="rounded-full bg-red-50 px-3 py-1.5 text-red-700">{{ $lowStockBooks->count() }} em estoque crítico</span>
                </div>
            </div>

            <details class="mx-auto max-w-3xl rounded-3xl border border-gray-100 bg-white shadow-sm" @if ($errors->any()) open @endif>
            <summary class="cursor-pointer list-none px-6 py-5 font-semibold text-gray-900">Cadastrar novo livro</summary>
            <form method="POST" action="{{ route('stock.books.store-new') }}" enctype="multipart/form-data" class="border-t border-gray-100 p-5 sm:p-6" x-data="bookRegistrationForm()">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900 mb-2">Título do livro</label>
                    <input type="text" name="title" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Curso / Área</label>
                    <input type="text" name="subject" list="course-options" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Busque um curso">
                    <datalist id="course-options">
                        @foreach ($cursos as $curso)
                            <option value="{{ $curso->nome_curso }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">ISBN (opcional)</label>
                    <input type="text" name="isbn" x-model="isbn" @input="maskIsbn()" inputmode="numeric" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Somente números e hífens">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Ano de publicação</label>
                    <input type="number" name="publication_year" value="{{ old('publication_year') }}" min="1900" max="{{ now()->year + 1 }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: {{ now()->year }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Páginas</label>
                    <input type="number" name="pages" value="{{ old('pages') }}" min="1" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: 240">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900 mb-2">Descrição</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none resize-none"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900 mb-2">Imagem da capa (opcional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-900 file:px-3 file:py-1.5 file:font-semibold file:text-white">
                </div>
                <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Estoque inicial</label>
                        <input type="number" name="quantity" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Estoque mínimo</label>
                        <input type="number" name="minimum_stock" min="1" value="{{ $stockCriticalThreshold }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <button type="submit" class="sm:col-span-2 w-full rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-800">Cadastrar livro</button>
                </div>
            </form>
            </details>

            <div class="mt-10 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm" x-data="bookEditTable(@js($booksArray))" data-native-table-filters>
                <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Livros cadastrados</h2>
                        <p class="mt-1 text-sm text-gray-500">Localize um livro e abra sua linha para editar.</p>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <input type="search" x-model="query" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none sm:w-64" placeholder="Buscar pelo nome do livro">
                        <select x-model="subject" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none sm:w-56">
                            <option value="">Todas as áreas</option>
                            <template x-for="area in subjects" :key="area">
                                <option :value="area" x-text="area"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">Livro</th>
                                <th class="px-4 py-3 font-medium">Área</th>
                                <th class="px-4 py-3 font-medium text-right">Estoque</th>
                                <th class="px-4 py-3 font-medium text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($booksArray as $book)
                                <tr x-show="matches(@js($book))" class="border-t border-gray-100 hover:bg-gray-50/70">
                                    <td class="max-w-xs truncate px-4 py-3 font-semibold text-gray-900">{{ $book['title'] }}</td>
                                    <td class="max-w-40 truncate px-4 py-3 text-gray-600">{{ $book['subject'] }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $book['quantity'] }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" @click="toggle({{ $book['id'] }})" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100" x-text="String(selectedId) === '{{ $book['id'] }}' ? 'Fechar' : 'Editar'"></button>
                                    </td>
                                </tr>
                                <tr x-show="matches(@js($book)) && String(selectedId) === '{{ $book['id'] }}'" x-cloak class="border-t border-gray-100 bg-gray-50/70">
                                    <td colspan="4" class="p-4">
                                        <form method="POST" action="{{ route('stock.books.update', $book['id']) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="title" value="{{ $book['title'] }}" required class="rounded-xl border border-gray-300 bg-white px-4 py-3" aria-label="Título">
                                            <input type="text" name="isbn" value="{{ $book['isbn'] }}" class="rounded-xl border border-gray-300 bg-white px-4 py-3" placeholder="ISBN">
                                            <input type="text" name="subject" value="{{ $book['subject'] }}" list="course-options" required class="rounded-xl border border-gray-300 bg-white px-4 py-3" aria-label="Curso ou área">
                                            <input type="number" name="minimum_stock" value="{{ $book['minimumStock'] }}" min="1" required class="rounded-xl border border-gray-300 bg-white px-4 py-3" aria-label="Estoque mínimo">
                                            <input type="number" name="publication_year" value="{{ $book['year'] }}" min="1900" max="{{ now()->year + 1 }}" class="rounded-xl border border-gray-300 bg-white px-4 py-3" placeholder="Ano de publicação" aria-label="Ano de publicação">
                                            <input type="number" name="pages" value="{{ $book['pages'] }}" min="1" class="rounded-xl border border-gray-300 bg-white px-4 py-3" placeholder="Páginas" aria-label="Páginas">
                                            <select name="status" class="rounded-xl border border-gray-300 bg-white px-4 py-3" aria-label="Status">
                                                <option value="ativo" @selected($book['status'] === 'ativo')>Ativo</option>
                                                <option value="inativo" @selected($book['status'] === 'inativo')>Inativo</option>
                                            </select>
                                            <input type="file" name="image" accept="image/*" class="rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm" aria-label="Nova imagem da capa">
                                            <textarea name="description" rows="3" class="rounded-xl border border-gray-300 bg-white px-4 py-3 md:col-span-2" aria-label="Descrição">{{ $book['desc'] }}</textarea>
                                            <button type="submit" class="rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white">Salvar alterações</button>
                                        </form>
                                        <form method="POST" action="{{ route('stock.books.destroy', $book['id']) }}" class="mt-3" onsubmit="return confirm('Excluir este livro? O histórico relacionado também poderá ser removido.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">Excluir livro</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="senai-empty-cell">Nenhum livro cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'library')
        <div class="animate-in fade-in duration-500" x-data="libraryBrowser(@js($booksArray), {{ $stockCriticalThreshold }})">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Catálogo de Livros</h1>
                <p class="text-gray-500 mt-1 text-base">Navegue pelo acervo por área e registre entradas ou saídas.</p>
            </div>

            <div id="book-details" x-show="selectedBook" x-cloak class="scroll-mt-6 mb-10 grid grid-cols-1 lg:grid-cols-12 gap-6 rounded-[28px] border border-gray-100 bg-white p-6 shadow-sm">
                <div class="lg:col-span-4">
                    <h2 class="mb-4 text-2xl font-semibold tracking-tight text-gray-900 lg:hidden" x-text="selectedBook?.title"></h2>
                    <img x-show="selectedBook?.imageUrl" :src="selectedBook?.imageUrl" :alt="selectedBook?.title" class="mx-auto max-h-[26rem] w-full rounded-3xl bg-gray-50 object-contain">
                    <div x-show="!selectedBook?.imageUrl" class="aspect-[3/4] rounded-3xl bg-gradient-to-br from-gray-100 to-gray-200 p-6 flex flex-col justify-between">
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-500" x-text="selectedBook?.subject"></span>
                        <div>
                            <p class="text-2xl font-semibold leading-tight text-gray-900" x-text="selectedBook?.title"></p>
                            <p class="mt-3 text-sm text-gray-500" x-text="selectedBook?.isbn"></p>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-5">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-400">Detalhes do material</p>
                    <h2 class="mt-2 hidden text-3xl font-semibold tracking-tight text-gray-900 lg:block" x-text="selectedBook?.title"></h2>
                    <p class="mt-4 text-gray-600 leading-relaxed" x-text="selectedBook?.desc"></p>
                    <dl class="mt-6 grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-400">ISBN</dt><dd class="font-semibold text-gray-900" x-text="selectedBook?.isbn"></dd></div>
                        <div><dt class="text-gray-400">Ano</dt><dd class="font-semibold text-gray-900" x-text="selectedBook?.year || 'Não informado'"></dd></div>
                        <div><dt class="text-gray-400">Páginas</dt><dd class="font-semibold text-gray-900" x-text="selectedBook?.pages || 'Não informado'"></dd></div>
                    </dl>
                </div>
                <div class="lg:col-span-3">
                    <div class="rounded-3xl border border-gray-100 bg-gray-50 p-5">
                        <p class="text-sm text-gray-500">Saldo atual</p>
                        <p class="mt-1 text-4xl font-semibold text-gray-900" x-text="selectedBook?.quantity"></p>
                        <p class="mt-2 text-xs font-bold uppercase" :class="isCritical(selectedBook) ? 'text-red-600' : 'text-emerald-600'" x-text="isCritical(selectedBook) ? 'Estoque crítico' : 'Estoque adequado'"></p>
                        @if ($employeeRole === 'Professor' || $can('stock.withdraw') || $can('purchases.create'))
                        <div class="mt-6 space-y-3">
                            @if ($can('teacher_requests.create'))
                                <a :href="`{{ route('senai.dashboard', ['view' => 'teacher_requests']) }}?book_id=${selectedBook?.id}`" class="block rounded-xl bg-red-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-red-700">Criar pedido deste livro</a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-10">
                @foreach ($booksArray->groupBy('subject') as $subject => $subjectBooks)
                    <section>
                        <div class="flex items-center justify-between px-2 mb-5">
                            <h3 class="text-xl font-semibold text-gray-900 tracking-tight">{{ $subject }}</h3>
                            <span class="text-sm font-medium text-gray-400">{{ $subjectBooks->count() }} itens</span>
                        </div>
                        <div class="flex gap-4 overflow-x-auto pb-3 snap-x">
                            @foreach ($subjectBooks as $book)
                                <div class="w-64 shrink-0 snap-start bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <button type="button" @click="select({{ $book['id'] }})" class="mb-4 aspect-[3/4] w-full overflow-hidden rounded-2xl bg-gray-100 flex items-end text-left transition hover:scale-[1.02]">
                                        @if ($book['imageUrl'])
                                            <img src="{{ $book['imageUrl'] }}" alt="{{ $book['title'] }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="p-4 text-lg font-semibold leading-tight text-gray-900">{{ $book['title'] }}</span>
                                        @endif
                                    </button>
                                    <div class="flex items-start justify-between mb-3">
                                        <h4 class="font-medium text-gray-900 text-sm leading-tight line-clamp-2 flex-1">{{ $book['title'] }}</h4>
                                        @if ($book['quantity'] < $stockCriticalThreshold)
                                            <span class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">CRÍTICO</span>
                                        @else
                                            <span class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">OK</span>
                                        @endif
                                    </div>
                                    <div class="mb-4">
                                        <p class="text-gray-600 text-xs mb-2">ISBN: <span class="font-mono">{{ $book['isbn'] }}</span></p>
                                        <p class="text-gray-900 font-semibold">{{ $book['quantity'] }} unidades disponíveis</p>
                                    </div>
                                    @if ($can('stock.receive') || $can('stock.withdraw'))
                                    <div class="flex gap-2">
                                        @if ($can('stock.receive'))
                                            <a href="{{ route('senai.dashboard', ['view' => 'stock', 'tab' => 'entrada']) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg text-xs font-medium transition-colors">
                                                ⬇ Entrada
                                            </a>
                                        @endif
                                        @if ($can('teacher_requests.create'))
                                            <a href="{{ route('senai.dashboard', ['view' => 'teacher_requests']) }}?book_id={{ $book['id'] }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors">
                                                Criar pedido
                                            </a>
                                        @endif
                                    </div>
                                    @endif
                                </div>

                                <!-- Modal Recebimento -->
                                <x-modal :name="'receive-book-' . $book['id']" :show="false" maxWidth="md">
                                    <div class="p-6">
                                        <div class="mb-6">
                                            <h2 class="text-xl font-semibold text-gray-900">Entrada de Estoque</h2>
                                            <p class="text-gray-600 text-sm mt-1">{{ $book['title'] }}</p>
                                        </div>

                                        <form x-data="{ submitting: false }" @submit.prevent="submitting = true; fetch('/api/books/{{ $book['id'] }}/receive', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ quantity: this.$refs.qty.value, notes: this.$refs.notes.value }) }).then(async (r) => { if (!r.ok) { const error = await r.json().catch(() => null); throw new Error(error?.message || 'Erro ao processar a entrada.'); } return r.json(); }).then(d => { alert(d.message); $dispatch('close-modal', 'receive-book-{{ $book['id'] }}'); location.reload(); }).catch(e => { alert('Erro: ' + e.message); submitting = false; })" class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900 mb-2">Quantidade a Receber</label>
                                                <input type="number" x-ref="qty" min="1" required placeholder="Ex: 10" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900 mb-2">Observações (Opcional)</label>
                                                <textarea x-ref="notes" rows="3" placeholder="Ex: Fornecedor, lote, etc." class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                                            </div>
                                            <div class="flex gap-3 justify-end pt-4">
                                                <button type="button" @click="$dispatch('close-modal', 'receive-book-{{ $book['id'] }}')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                                                <button type="submit" :disabled="submitting" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium transition-colors">
                                                    <span x-show="!submitting">Confirmar Entrada</span>
                                                    <span x-show="submitting">Processando...</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </x-modal>

                                <!-- Modal Saída -->
                                <x-modal :name="'withdraw-book-' . $book['id']" :show="false" maxWidth="md">
                                    <div class="p-6">
                                        <div class="mb-6">
                                            <h2 class="text-xl font-semibold text-gray-900">Saída de Estoque</h2>
                                            <p class="text-gray-600 text-sm mt-1">{{ $book['title'] }}</p>
                                            <p class="text-gray-500 text-xs mt-2">Disponível: <strong>{{ $book['quantity'] }} unidades</strong></p>
                                        </div>

                                        <form x-data="{ submitting: false, qty: 0 }" @submit.prevent="submitting = true; fetch('/api/books/{{ $book['id'] }}/withdraw', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ quantity: this.$refs.qty.value, justification: this.$refs.justif.value }) }).then(async (r) => { if (!r.ok) { const error = await r.json().catch(() => null); throw new Error(error?.message || 'Erro ao processar a saída.'); } return r.json(); }).then(d => { alert(d.message); $dispatch('close-modal', 'withdraw-book-{{ $book['id'] }}'); location.reload(); }).catch(e => { alert('Erro: ' + e.message); submitting = false; })" class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900 mb-2">Quantidade a Retirar</label>
                                                <input type="number" x-ref="qty" x-model.number="qty" min="1" max="{{ $book['quantity'] }}" required placeholder="Ex: 5" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none">
                                                <p class="text-xs text-gray-500 mt-1">Máximo: {{ $book['quantity'] }} unidades</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900 mb-2">Justificativa <span class="text-red-500">*</span></label>
                                                <textarea x-ref="justif" rows="3" required placeholder="Ex: Saída para turma de Mecânica, sala 101" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-900 focus:ring-2 focus:ring-orange-500 outline-none resize-none"></textarea>
                                            </div>
                                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                                                <p class="text-xs text-orange-700"><strong>⚠</strong> Esta ação não pode ser desfeita. Verifique os dados antes de confirmar.</p>
                                            </div>
                                            <div class="flex gap-3 justify-end pt-4">
                                                <button type="button" @click="$dispatch('close-modal', 'withdraw-book-{{ $book['id'] }}')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                                                <button type="submit" :disabled="submitting || qty <= 0 || qty > {{ $book['quantity'] }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium transition-colors">
                                                    <span x-show="!submitting">Confirmar Saída</span>
                                                    <span x-show="submitting">Processando...</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </x-modal>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    @elseif ($activeView === 'stock')
        <div class="animate-in fade-in duration-500" x-data="preservedTabs(@js($activeTab ?? 'entrada'))">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Entrada e Saída</h1>
                <p class="text-gray-500 mt-1 text-base">Registre recebimentos, retiradas para turmas e consulte o histórico de movimentações.</p>
            </div>

            <div class="flex p-1 bg-gray-100 rounded-xl mb-8 w-full sm:w-fit">
                <button type="button" @click="selectTab('entrada')" class="px-5 py-2.5 text-sm font-medium rounded-lg" :class="tab === 'entrada' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Entrada</button>
                <button type="button" @click="selectTab('saida')" class="px-5 py-2.5 text-sm font-medium rounded-lg" :class="tab === 'saida' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Saída</button>
                <button type="button" @click="selectTab('historico')" class="px-5 py-2.5 text-sm font-medium rounded-lg" :class="tab === 'historico' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Histórico</button>
            </div>

            <div x-show="tab === 'entrada'" x-cloak class="max-w-3xl mx-auto space-y-6">
            @php
                $ordersToReceive = $purchaseOrders
                    ->whereIn('status', ['aprovado', 'recebimento_parcial'])
                    ->filter(fn ($order) => !empty($order['id']));
            @endphp
            @if ($ordersToReceive->isNotEmpty())
                <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Recebimentos de compras aprovadas</h2>
                    <p class="mt-1 text-sm text-gray-500">Informe a quantidade realmente recebida. O restante continuará pendente.</p>
                    <div class="mt-5 space-y-4">
                        @foreach ($ordersToReceive as $order)
                            @foreach (collect($order['items'])->where('remainingQty', '>', 0) as $item)
                                <form method="POST" action="{{ route('stock.purchases.items.receive', [$order['id'], $item['id']]) }}" class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-4">
                                    @csrf
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900">{{ $item['title'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $order['orderId'] }} · pendente: {{ $item['remainingQty'] }} un</p>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold text-gray-500">Quantidade recebida</label>
                                            <input type="number" name="quantity" min="1" max="{{ $item['remainingQty'] }}" required class="w-full rounded-xl border-2 border-emerald-300 bg-white px-4 py-3.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none sm:w-40">
                                        </div>
                                        <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Registrar entrada</button>
                                    </div>
                                </form>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($ordersToReceive->isEmpty())
                <div class="senai-empty">Nenhuma compra aprovada aguardando entrada.</div>
            @endif
            </div>

            <div x-show="tab === 'saida'" x-cloak class="max-w-3xl mx-auto space-y-6" x-data='withdrawCartForm(@js($booksArray), @js($turmaOptions))'>
            @php
                $requestsToFulfill = $teacherRequests
                    ->whereIn('status', ['aprovado', 'separado_parcial'])
                    ->where('remaining', '>', 0);
            @endphp
            @if ($requestsToFulfill->isNotEmpty())
                <div class="rounded-3xl border border-red-100 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Retiradas de pedidos</h2>
                    <p class="mt-1 text-sm text-gray-500">Registre a quantidade realmente retirada. É possível concluir o pedido em partes.</p>
                    <div class="mt-5 space-y-4">
                        @foreach ($requestsToFulfill as $request)
                            @php $maxFulfill = min((int) $request['remaining'], (int) $request['available']); @endphp
                            <form method="POST" action="{{ route('stock.teacher-requests.fulfill', $request['id']) }}" class="rounded-2xl border border-red-200 bg-red-50/40 p-4">
                                @csrf
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">{{ $request['title'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $request['teacher'] }} · {{ $request['turma'] }} · restante: {{ $request['remaining'] }} un</p>
                                        <p class="mt-1 text-xs font-medium {{ $maxFulfill > 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ $maxFulfill > 0 ? $maxFulfill . ' un disponível(is) para retirada agora' : 'Aguardando entrada de estoque' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-gray-500">Quantidade retirada</label>
                                        <input type="number" name="quantity" min="1" max="{{ $maxFulfill }}" required @disabled($maxFulfill < 1) class="w-full rounded-xl border-2 border-red-300 bg-white px-4 py-3.5 text-sm text-gray-900 focus:border-red-500 focus:ring-2 focus:ring-red-500 outline-none disabled:opacity-50 sm:w-40">
                                    </div>
                                    <button type="submit" @disabled($maxFulfill < 1) class="rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-40">Registrar saída</button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($requestsToFulfill->isEmpty())
                <div class="senai-empty">Nenhum pedido aprovado aguardando separação.</div>
            @endif
            </div>

            <div x-show="tab === 'historico'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium">Entradas recentes</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-700">{{ $movements->where('type', 'entrada')->sum('quantity') }}</p>
                </div>
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium">Saídas recentes</p>
                    <p class="mt-2 text-3xl font-semibold text-red-700">{{ $movements->where('type', 'saida')->sum('quantity') }}</p>
                </div>
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium">Registros listados</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $movements->count() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Auditoria do estoque</h2>
                    <span class="text-sm text-gray-400">últimos 60 lançamentos</span>
                </div>
                <div class="overflow-x-auto">
                    <table
                        class="w-full text-left text-sm whitespace-nowrap"
                        data-table-search-placeholder="Buscar material ou responsável"
                        data-table-filter-column="0"
                        data-table-filter-label="Todos os tipos"
                    >
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Tipo</th>
                                <th class="px-6 py-4 font-medium">Material</th>
                                <th class="px-6 py-4 font-medium text-right">Qtd.</th>
                                <th class="px-6 py-4 font-medium">Responsável</th>
                                <th class="px-6 py-4 font-medium">Justificativa</th>
                                <th class="px-6 py-4 font-medium text-right">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr class="border-t border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $movement->type === 'entrada' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                            {{ $movement->type === 'entrada' ? 'Entrada' : 'Saída' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $movement->book?->title ?? 'Material removido' }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ $movement->quantity }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $movement->funcionario?->Nome ?? data_get($employee, 'name', 'Sistema') }}</td>
                                    <td class="px-6 py-4 text-gray-500 max-w-sm truncate">{{ $movement->justification ?: 'Sem observação' }}</td>
                                    <td class="px-6 py-4 text-right text-xs font-mono text-gray-400">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="senai-empty-cell">Nenhuma movimentação registrada ainda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    @elseif ($activeView === 'alerts')
        <div class="animate-in fade-in duration-500">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Alertas Operacionais</h1>
                <p class="text-gray-500 mt-1 text-base">Tudo que precisa de ação antes de virar falta de material.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('senai.dashboard', ['view' => 'reports']) }}" class="bg-red-50 rounded-3xl border border-red-100 p-6 hover:bg-red-100/60 transition">
                    <p class="text-sm text-red-700 font-medium">Estoque crítico</p>
                    <p class="mt-2 text-4xl font-semibold text-red-700">{{ $lowStockBooks->count() }}</p>
                </a>
                <a href="{{ route('senai.dashboard', ['view' => 'teacher_requests']) }}" class="bg-amber-50 rounded-3xl border border-amber-100 p-6 hover:bg-amber-100/60 transition">
                    <p class="text-sm text-amber-700 font-medium">Pedidos pendentes</p>
                    <p class="mt-2 text-4xl font-semibold text-amber-700">{{ $pendingRequests->count() }}</p>
                </a>
                @if ($canView('purchases'))
                <a href="{{ route('senai.dashboard', ['view' => 'purchases', 'tab' => 'historico']) }}" class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:bg-gray-50 transition">
                    <p class="text-sm text-gray-500 font-medium">Compras abertas</p>
                    <p class="mt-2 text-4xl font-semibold text-gray-900">{{ $purchaseOrders->where('status', 'aguardando')->count() }}</p>
                </a>
                @endif
            </div>

            <div class="space-y-4">
                @forelse ($alerts as $alert)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row md:items-center gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase {{ $alert['severity'] === 'critical' ? 'bg-red-50 text-red-700 border border-red-100' : ($alert['severity'] === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-100' : 'bg-gray-50 text-gray-600 border border-gray-100') }}">
                                    {{ $alert['type'] === 'stock' ? 'Estoque' : ($alert['type'] === 'request' ? 'Pedido' : 'Compra') }}
                                </span>
                                <h2 class="font-semibold text-gray-900">{{ $alert['title'] }}</h2>
                            </div>
                            <p class="text-sm text-gray-500">{{ $alert['message'] }}</p>
                        </div>
                        @if (($alert['type'] === 'stock' && $can('alerts.purchase')) || ($alert['type'] === 'request' && $can('teacher_requests.purchase')) || ($alert['type'] === 'purchase' && $canView('purchases')))
                        <div class="w-full md:w-auto">
                            @if ($alert['type'] === 'stock' && $can('alerts.purchase'))
                                <form method="POST" action="{{ route('stock.alerts.purchase', $alert['bookId']) }}">
                                    @csrf
                                    <button type="submit" class="w-full md:w-auto rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-800">{{ $alert['action'] }}</button>
                                </form>
                            @elseif ($alert['type'] === 'request' && $can('teacher_requests.purchase'))
                                <form method="POST" action="{{ route('stock.teacher-requests.purchase', $alert['requestId']) }}">
                                    @csrf
                                    <button type="submit" class="w-full md:w-auto rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-600">{{ $alert['action'] }}</button>
                                </form>
                            @elseif ($alert['type'] === 'purchase' && $canView('purchases'))
                                <a href="{{ route('senai.dashboard', ['view' => 'purchases', 'tab' => 'historico']) }}" class="block rounded-xl border border-gray-200 px-4 py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">{{ $alert['action'] }}</a>
                            @endif
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="senai-empty">Sem alertas no momento.</div>
                @endforelse
            </div>
        </div>
    @elseif ($activeView === 'suppliers')
        <div class="animate-in fade-in duration-500 max-w-3xl">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Fornecedor</h1>
                <p class="text-gray-500 mt-1 text-base">Todos os pedidos de compra são enviados à Editora Senai.</p>
            </div>

            @if ($defaultSupplier)
                <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        <h2 class="text-2xl font-semibold text-gray-900">{{ $defaultSupplier->name }}</h2>
                        <span class="rounded-full px-3 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Fornecedor oficial</span>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <dt class="text-gray-400">Contato</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $defaultSupplier->contact_name ?: 'Atendimento Corporativo' }}</dd>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <dt class="text-gray-400">Prazo médio</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $defaultSupplier->lead_time_days }} dias</dd>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <dt class="text-gray-400">E-mail</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $defaultSupplier->email ?: 'pedidos@editorasenai.com.br' }}</dd>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <dt class="text-gray-400">Telefone</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $defaultSupplier->phone ?: '(11) 3000-0101' }}</dd>
                        </div>
                    </dl>
                    <p class="mt-6 text-sm text-gray-500">O sistema utiliza exclusivamente este fornecedor para reposição e novos títulos.</p>
                </div>
            @else
                <div class="senai-empty">Fornecedor padrão não configurado.</div>
            @endif
        </div>
    @elseif ($activeView === 'courses')
        <div class="animate-in fade-in duration-500 max-w-5xl" x-data="{ courseSearch: '' }">
            <div class="senai-school-hero">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Escola · Organização</p>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Curso</h1>
                <p class="text-gray-500 mt-1 text-base">Crie as áreas usadas para relacionar livros e turmas.</p>
                <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold text-gray-600">
                    <span class="rounded-full bg-gray-100 px-3 py-1.5">{{ $cursos->count() }} cursos</span>
                    <span class="rounded-full bg-red-50 px-3 py-1.5 text-red-700">{{ $turmas->count() }} turmas vinculadas</span>
                </div>
            </div>

            <details class="mb-8 rounded-3xl border border-gray-100 bg-white shadow-sm">
                <summary class="cursor-pointer list-none px-6 py-5 text-lg font-semibold text-gray-900">Cadastrar novo curso</summary>
                <form method="POST" action="{{ route('stock.courses.store') }}" class="grid grid-cols-1 gap-5 border-t border-gray-100 p-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Nome do curso / área</label>
                        <input type="text" name="nome_curso" value="{{ old('nome_curso') }}" required autofocus class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: Desenvolvimento de Sistemas">
                        @error('nome_curso')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <button type="submit" class="rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">Cadastrar curso</button>
                    </div>
                </form>
            </details>

            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Cursos cadastrados</h2>
                    <input type="search" x-model="courseSearch" class="senai-table-filter" placeholder="Buscar curso">
                </div>
                <div class="grid grid-cols-1 gap-3 p-6 sm:grid-cols-2">
                    @forelse ($cursos as $curso)
                        <div x-show="@js(strtolower($curso->nome_curso)).includes(courseSearch.toLowerCase())" class="senai-surface-hover rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            @php $courseFormId = 'course-' . $curso->id; @endphp
                            <form id="{{ $courseFormId }}" method="POST" action="{{ route('stock.courses.update', $curso) }}">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nome_curso" value="{{ $curso->nome_curso }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 font-semibold text-gray-900">
                            </form>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-xs text-gray-500">{{ $curso->turmas_count ?? $turmas->where('curso_id', $curso->id)->count() }} turma(s)</p>
                                <div class="flex items-center gap-2">
                                    <button form="{{ $courseFormId }}" type="submit" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white">Salvar</button>
                                    <form method="POST" action="{{ route('stock.courses.destroy', $curso) }}" onsubmit="return confirm('Excluir este curso?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum curso cadastrado.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @elseif ($activeView === 'classes')
        <div class="animate-in fade-in duration-500" x-data="{ classSearch: '', courseFilter: '' }">
            <div class="senai-school-hero">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Escola · Turmas</p>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Turmas</h1>
                <p class="text-gray-500 mt-1 text-base">Mapa rápido das turmas usadas como destino nas retiradas.</p>
            </div>

            @if ($can('classes.manage'))
            <details class="mb-8 rounded-3xl border border-gray-100 bg-white shadow-sm">
                <summary class="cursor-pointer list-none px-6 py-5 text-lg font-semibold text-gray-900">Cadastrar nova turma</summary>
                <form method="POST" action="{{ route('stock.classes.store') }}" class="grid grid-cols-1 gap-5 border-t border-gray-100 p-6 md:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Nome da turma</label>
                        <input type="text" name="nome_turma" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: MEC-2A">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Curso existente</label>
                        <select name="curso_id" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                            <option value="">Selecione um curso...</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id }}">{{ $curso->nome_curso }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">Cadastrar turma</button>
                    </div>
                </form>
            </details>
            @endif

            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="senai-school-stat flex items-center gap-3">
                    <span class="text-2xl font-semibold text-gray-900">{{ $turmas->count() }}</span>
                    <span class="text-sm font-medium text-gray-500">turmas cadastradas</span>
                </div>
                <div class="senai-school-stat flex items-center gap-3">
                    <span class="text-2xl font-semibold text-gray-900">{{ $turmas->pluck('curso_id')->unique()->count() }}</span>
                    <span class="text-sm font-medium text-gray-500">cursos com turmas</span>
                </div>
            </div>

            <div class="mb-5 flex flex-col gap-2 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row">
                <input type="search" x-model="classSearch" class="senai-table-filter" placeholder="Buscar turma">
                <select x-model="courseFilter" class="senai-table-filter">
                    <option value="">Todos os cursos</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->nome_curso }}">{{ $curso->nome_curso }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-4">
                @forelse ($turmas->groupBy(fn ($turma) => $turma->curso?->nome_curso ?? 'Sem curso') as $courseName => $courseClasses)
                    <details open x-show="(!courseFilter || courseFilter === @js($courseName)) && (!classSearch || @js(strtolower($courseClasses->pluck('nome_turma')->join(' '))).includes(classSearch.toLowerCase()))" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <summary class="flex cursor-pointer list-none items-center justify-between border-b border-gray-100 bg-gray-50/70 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-900">{{ $courseName }}</h2>
                            <span class="text-xs font-medium text-gray-400">{{ $courseClasses->count() }} turma(s)</span>
                        </summary>
                        <div class="divide-y divide-gray-100">
                            @foreach ($courseClasses as $turma)
                                <div class="flex flex-col gap-2 px-4 py-3 md:flex-row md:items-center">
                                    <form method="POST" action="{{ route('stock.classes.update', $turma) }}" class="grid flex-1 grid-cols-1 gap-2 sm:grid-cols-[minmax(10rem,1fr)_minmax(12rem,1fr)_auto]">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="nome_turma" value="{{ $turma->nome_turma }}" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-900">
                                        <select name="curso_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                            @foreach ($cursos as $curso)
                                                <option value="{{ $curso->id }}" @selected($curso->id === $turma->curso_id)>{{ $curso->nome_curso }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white">Salvar</button>
                                    </form>
                                    <form method="POST" action="{{ route('stock.classes.destroy', $turma) }}" onsubmit="return confirm('Excluir esta turma?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Excluir</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @empty
                    <div class="senai-empty">Nenhuma turma cadastrada.</div>
                @endforelse
            </div>
        </div>
    @elseif ($activeView === 'people')
        <div class="animate-in fade-in duration-500" x-data="{ search: '', role: 'todos' }">
            <div class="senai-school-hero flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Escola · Equipe</p>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Equipe</h1>
                    <p class="text-gray-500 mt-1 text-base">Consulte acessos e gerencie os colaboradores do sistema.</p>
                </div>
                @if ($can('people.manage'))
                <div class="flex gap-3">
                    <a href="{{ route('funcionarios.create') }}" class="rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-800">Novo funcionário</a>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="senai-school-stat">
                    <p class="text-sm text-gray-500 font-medium">Funcionários</p>
                    <p class="mt-2 text-4xl font-semibold text-gray-900">{{ $funcionarios->count() }}</p>
                </div>
                <div class="senai-school-stat">
                    <p class="text-sm text-gray-500 font-medium">Coordenadores</p>
                    <p class="mt-2 text-4xl font-semibold text-gray-900">{{ $funcionarios->filter(fn ($item) => $item->cargo?->Nome_cargo === 'Coordenador')->count() }}</p>
                </div>
                <div class="senai-school-stat">
                    <p class="text-sm text-gray-500 font-medium">Professores</p>
                    <p class="mt-2 text-4xl font-semibold text-gray-900">{{ $funcionarios->filter(fn ($item) => $item->cargo?->Nome_cargo === 'Professor')->count() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm" data-native-table-filters>
                <div class="px-6 py-5 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Lista da equipe</h2>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="search" x-model="search" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm" placeholder="Buscar nome ou NIF">
                        <select x-model="role" class="min-w-48 appearance-none rounded-xl border border-gray-200 bg-gray-50 bg-none px-4 py-2 pr-4 text-sm">
                            <option value="todos">Todos os acessos</option>
                            <option value="Coordenador">Coordenadores</option>
                            <option value="Professor">Professores</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Nome</th>
                                <th class="px-6 py-4 font-medium">NIF</th>
                                <th class="px-6 py-4 font-medium">Cargo</th>
                                <th class="px-6 py-4 font-medium text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($funcionarios as $funcionario)
                                <tr class="border-t border-gray-50 hover:bg-gray-50/60" x-show="(role === 'todos' || role === @js($funcionario->cargo?->Nome_cargo)) && (@js(strtolower($funcionario->Nome.' '.$funcionario->NIF)).includes(search.toLowerCase()))">
                                    @php $employeeFormId = 'employee-' . $funcionario->Id_funcionario; @endphp
                                    <td class="px-4 py-3">
                                        <input form="{{ $employeeFormId }}" type="text" name="Nome" value="{{ $funcionario->Nome }}" required class="w-full min-w-40 rounded-lg border border-gray-200 bg-white px-3 py-2 font-semibold text-gray-900">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input form="{{ $employeeFormId }}" type="number" name="NIF" value="{{ $funcionario->NIF }}" required class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2 text-gray-700">
                                    </td>
                                    <td class="px-4 py-3">
                                        <select form="{{ $employeeFormId }}" name="Id_cargo_FK" required class="min-w-36 rounded-lg border border-gray-200 bg-white px-3 py-2 text-gray-700">
                                            @foreach ($cargos->whereIn('Nome_cargo', ['Coordenador', 'Professor']) as $cargo)
                                                <option value="{{ $cargo->Id_cargo }}" @selected($cargo->Id_cargo === $funcionario->Id_cargo_FK)>{{ $cargo->Nome_cargo }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <form id="{{ $employeeFormId }}" method="POST" action="{{ route('funcionarios.update', $funcionario) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white">Salvar</button>
                                            </form>
                                            <form method="POST" action="{{ route('funcionarios.destroy', $funcionario) }}" onsubmit="return confirm('Excluir este funcionário?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="senai-empty-cell">Nenhum funcionário cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
