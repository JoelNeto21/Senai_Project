<x-app-layout
    :active-view="$activeView"
    :navigation-items="$navigationItems"
    :employee="$employee"
    :purchase-cart-count="$purchaseCartCount"
    :withdraw-cart-count="$withdrawCartCount"
    :pending-teacher-requests="$pendingTeacherRequests"
>
    @php
        $booksArray = $books->values();
        $lowStockBooks = $booksArray->filter(fn ($book) => $book['quantity'] < $stockCriticalThreshold)->values();
        $booksBySubject = $booksArray->groupBy('subject')->map(fn ($group) => $group->sum('quantity'))->sortDesc();
        $turmaOptions = $turmas->map(fn ($turma) => [
            'id' => $turma->id,
            'nome_turma' => $turma->nome_turma,
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

        $pendingRequests = $teacherRequests->where('status', 'pendente');
        $processedRequests = $teacherRequests->where('status', '!=', 'pendente');
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
                <div class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-red-50 text-red-600 text-sm font-medium mb-6 border border-red-100">
                    Novos recursos adicionados
                </div>
                <h1 class="text-4xl md:text-5xl font-semibold tracking-tight text-gray-900 mb-4">{{ $greeting }}, Almoxarifado.</h1>
                <p class="text-lg text-gray-500 font-medium">O que você está procurando hoje?</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium mb-1">Atenção Necessária</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $lowStockBooks->count() }} títulos</p>
                    <p class="text-sm text-gray-400 mt-1">com estoque abaixo de {{ $stockCriticalThreshold }} unidades</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium mb-1">Volume Total</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $totalQuantity }} exemplares</p>
                    <p class="text-sm text-gray-400 mt-1">disponíveis no acervo</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium mb-1">Funcionários</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $funcionarios->count() }}</p>
                    <p class="text-sm text-gray-400 mt-1">cadastrados no banco</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium mb-1">Pedidos Pendentes</p>
                    <p class="text-3xl font-semibold text-gray-900">{{ $pendingTeacherRequests }}</p>
                    <p class="text-sm text-gray-400 mt-1">aguardando separação</p>
                </div>
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
        </div>
    @elseif ($activeView === 'overview')
        <div class="animate-in fade-in duration-500">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Visão Geral</h1>
                <p class="text-gray-500 mt-1 text-base">Resumo do seu acervo e da estrutura já cadastrada no sistema.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium mb-1">Títulos Diferentes</p>
                    <p class="text-4xl font-semibold text-gray-900">{{ $booksArray->count() }}</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium mb-1">Total de Exemplares</p>
                    <p class="text-4xl font-semibold text-gray-900">{{ $totalQuantity }}</p>
                </div>
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-sm text-gray-500 font-medium mb-1">Cargos</p>
                    <p class="text-4xl font-semibold text-gray-900">{{ $cargos->count() }}</p>
                </div>
                <div class="bg-red-50/60 rounded-3xl p-6 border border-red-100">
                    <p class="text-sm text-red-600 font-medium mb-1">Requer Atenção</p>
                    <p class="text-4xl font-semibold text-red-700">{{ $lowStockBooks->count() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Baixo Estoque</h2>
                    <span class="text-sm text-gray-400">limite crítico abaixo de {{ $stockCriticalThreshold }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium">Livro</th>
                                <th class="px-6 py-4 font-medium">Área</th>
                                <th class="px-6 py-4 font-medium text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockBooks as $book)
                                <tr class="border-t border-gray-50 hover:bg-gray-50/60">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-900">{{ $book['title'] }}</p>
                                        <p class="text-xs text-gray-500">ISBN: {{ $book['isbn'] }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ $book['subject'] }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 font-medium">{{ $book['quantity'] }} un</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-gray-500">Nenhum item em estado crítico.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'dashboard')
        <div class="animate-in fade-in duration-500">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Dashboard</h1>
                    <p class="text-gray-500 mt-1 text-base">Análises detalhadas e tabela mestra do acervo.</p>
                </div>
                <button type="button" class="flex items-center px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-medium transition-colors">
                    Exportar PDF
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Volume em Estoque por Área</h3>
                    <div class="space-y-4">
                        @foreach ($booksBySubject as $subject => $qty)
                            <div class="flex items-center text-sm gap-3">
                                <span class="w-28 text-gray-500 font-medium truncate">{{ $subject }}</span>
                                <div class="flex-1 bg-gray-50 rounded-full h-3 overflow-hidden">
                                    <div class="bg-gray-900 h-full rounded-full" style="width: {{ max(($qty / max($booksBySubject->max(), 1)) * 100, 15) }}%"></div>
                                </div>
                                <span class="w-12 text-right font-semibold text-gray-900">{{ $qty }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Saúde do Acervo</h3>
                    <div class="grid grid-cols-2 gap-4 h-full items-end">
                        <div class="rounded-2xl bg-emerald-50 p-5 text-center">
                            <p class="text-sm text-emerald-700 font-medium mb-2">Adequado</p>
                            <p class="text-4xl font-semibold text-emerald-700">{{ $booksArray->where('quantity', '>=', $stockCriticalThreshold)->count() }}</p>
                        </div>
                        <div class="rounded-2xl bg-red-50 p-5 text-center">
                            <p class="text-sm text-red-700 font-medium mb-2">Crítico</p>
                            <p class="text-4xl font-semibold text-red-700">{{ $lowStockBooks->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Tabela Mestra de Livros</h2>
                    <span class="text-sm text-gray-400">{{ $booksArray->count() }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-4 py-3.5 font-medium border-r border-gray-200 w-16 text-center">ID</th>
                                <th class="px-4 py-3.5 font-medium border-r border-gray-200 w-32">ISBN</th>
                                <th class="px-4 py-3.5 font-medium border-r border-gray-200 min-w-[280px]">Título da Obra</th>
                                <th class="px-4 py-3.5 font-medium border-r border-gray-200">Área</th>
                                <th class="px-4 py-3.5 font-medium border-r border-gray-200 text-right w-24">Qtd.</th>
                                <th class="px-4 py-3.5 font-medium text-center w-32">Status Operacional</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($booksArray as $book)
                                <tr class="border-t border-gray-50 hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-2.5 border-r border-gray-100 text-center text-gray-400 font-mono text-xs">{{ str_pad((string) $book['id'], 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-4 py-2.5 border-r border-gray-100 text-gray-500 font-mono text-xs">{{ $book['isbn'] }}</td>
                                    <td class="px-4 py-2.5 border-r border-gray-100 text-gray-900 font-medium truncate max-w-[300px]">{{ $book['title'] }}</td>
                                    <td class="px-4 py-2.5 border-r border-gray-100 text-gray-600">{{ $book['subject'] }}</td>
                                    <td class="px-4 py-2.5 border-r border-gray-100 text-right font-semibold text-gray-800">{{ $book['quantity'] }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $book['quantity'] < $stockCriticalThreshold ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                            {{ $book['quantity'] < $stockCriticalThreshold ? 'ESTOQUE CRÍTICO' : 'ADEQUADO' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'teacher_requests')
        <div class="animate-in fade-in duration-500">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Pedidos de Professores</h1>
                <p class="text-gray-500 mt-1 text-base">Solicitações ligadas ao acervo e à separação manual do material.</p>
            </div>

            <div class="space-y-4 mb-12">
                @forelse ($pendingRequests as $request)
                    <div class="bg-white rounded-[20px] border border-gray-200/80 shadow-sm overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            <div class="p-5 lg:col-span-3 border-b lg:border-b-0 lg:border-r border-gray-100 bg-gray-50/30">
                                <p class="font-semibold text-gray-900">{{ $request['teacher'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $request['subject'] }}</p>
                                <p class="text-[11px] text-gray-400 mt-4">#{{ $request['id'] }} • {{ $request['date'] }} às {{ $request['time'] }}</p>
                            </div>
                            <div class="p-5 lg:col-span-6">
                                <h3 class="font-semibold text-gray-900 text-lg mb-2">{{ $request['title'] }}</h3>
                                <p class="text-sm text-gray-600">Quantidade solicitada: <span class="font-semibold text-gray-900">{{ $request['qty'] }} un</span></p>
                            </div>
                            <div class="p-5 lg:col-span-3 flex flex-col gap-3 justify-center bg-gray-50/40">
                                <a href="mailto:{{ $request['email'] }}?subject=Sobre o pedido de material didático: {{ $request['title'] }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 px-4 py-2.5 rounded-xl text-center">Enviar E-mail</a>
                                <button type="button" class="text-sm font-medium bg-gray-900 text-white px-4 py-2.5 rounded-xl opacity-70 cursor-not-allowed" disabled>Separar Estoque</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-3xl border border-gray-100 p-10 text-center text-gray-500">Nenhum pedido pendente no momento.</div>
                @endforelse
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Histórico de Pedidos Processados</h2>
                    <span class="text-sm text-gray-400">{{ $processedRequests->count() }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                        <thead class="bg-gray-50/80 text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-medium border-r border-gray-100">Professor / Solicitante</th>
                                <th class="px-6 py-4 font-medium border-r border-gray-100">Título Solicitado</th>
                                <th class="px-6 py-4 font-medium border-r border-gray-100 text-center w-24">Qtd.</th>
                                <th class="px-6 py-4 font-medium border-r border-gray-100 text-center w-36">Status</th>
                                <th class="px-6 py-4 font-medium w-36">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($processedRequests as $request)
                                <tr class="border-t border-gray-50 hover:bg-gray-50/50">
                                    <td class="px-6 py-4 border-r border-gray-50 font-medium text-gray-900">{{ $request['teacher'] }} <span class="text-xs text-gray-400 font-normal ml-1">({{ $request['subject'] }})</span></td>
                                    <td class="px-6 py-4 border-r border-gray-50 text-gray-700">{{ $request['title'] }}</td>
                                    <td class="px-6 py-4 border-r border-gray-50 text-center font-semibold text-gray-700">{{ $request['qty'] }}</td>
                                    <td class="px-6 py-4 border-r border-gray-50 text-center">
                                        <span class="text-[10px] uppercase font-bold px-2 py-1 rounded border bg-emerald-50 text-emerald-600 border-emerald-200">Atendido</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $request['date'] }} {{ $request['time'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">Nenhum pedido processado encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'purchases')
        <div class="animate-in fade-in duration-500" x-data="purchaseCartForm(@js($booksArray))">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Gestão de Compras</h1>
                <p class="text-gray-500 mt-1 text-base">Monte a lista de reposição antes de gerar a ordem de compra.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 space-y-6">
                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-4 flex flex-col sm:flex-row gap-4 sm:items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-gray-100 text-gray-600">
                                    <span x-text="item.type === 'restock' ? 'R' : 'N'"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate" x-text="item.title"></p>
                                    <p class="text-xs uppercase tracking-wider text-gray-400" x-text="item.type === 'restock' ? 'Reposição' : 'Título Inédito'"></p>
                                </div>
                                <div class="flex gap-3 w-full sm:w-auto">
                                    <input type="text" x-model="item.justification" class="w-full sm:w-80 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Justificativa">
                                    <input type="number" min="1" x-model.number="item.quantity" class="w-24 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2 text-sm text-center focus:ring-2 focus:ring-red-500 outline-none">
                                    <button type="button" @click="removeItem(index)" class="px-3 py-2 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50">Remover</button>
                                </div>
                            </div>
                        </template>

                        <div x-show="items.length === 0" class="bg-white border border-dashed border-gray-200 rounded-2xl p-10 text-center text-gray-500">
                            Sua lista de compras está vazia.
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Adicionar à Lista Manualmente</h2>
                        <div class="flex p-1 bg-gray-100 rounded-xl mb-6 w-full sm:w-fit">
                            <button type="button" @click="reqType = 'restock'" class="px-6 py-2.5 text-sm font-medium rounded-lg" :class="reqType === 'restock' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Repor Estoque</button>
                            <button type="button" @click="reqType = 'new'" class="px-6 py-2.5 text-sm font-medium rounded-lg" :class="reqType === 'new' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Título Inédito</button>
                        </div>

                        <form @submit.prevent="addItem" class="space-y-5">
                            <div x-show="reqType === 'restock'">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Selecionar Obra</label>
                                <select x-model="bookId" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none">
                                    <option value="">Escolha um título cadastrado...</option>
                                    <template x-for="book in catalog" :key="book.id">
                                        <option :value="book.id" x-text="`${book.title} (Estoque: ${book.quantity})`"></option>
                                    </template>
                                </select>
                            </div>

                            <div x-show="reqType === 'new'">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Nome da Obra / Assunto</label>
                                <input type="text" x-model="newTitle" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: Introdução ao Desenho 3D">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Quantidade de Compra</label>
                                    <input type="number" min="1" x-model.number="quantity" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: 50">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-2">Fornecedor / Editora</label>
                                    <input type="text" x-model="justification" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: Editora Érica">
                                </div>
                            </div>

                            <button type="submit" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 font-medium text-gray-900 transition-all hover:bg-gray-50">Adicionar à Lista</button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="sticky top-24 bg-white border border-gray-100 shadow-sm rounded-3xl p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6 border-b border-gray-100 pb-4">Resumo da Compra</h2>
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Títulos diferentes</span>
                                <span class="font-semibold text-gray-900" x-text="items.length"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Total de exemplares</span>
                                <span class="font-semibold text-gray-900 text-xl" x-text="totalItems"></span>
                            </div>
                        </div>
                        <button type="button" class="w-full font-medium py-4 px-4 rounded-xl transition-colors flex items-center justify-center bg-gray-900 hover:bg-gray-800 text-white" :disabled="items.length === 0">
                            Gerar Planilha de Pedido
                        </button>
                        <p class="text-xs text-center text-gray-400 mt-4 leading-relaxed">O carrinho é controlado no navegador nesta etapa. A gravação no banco vem no próximo passo.</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'history')
        <div class="animate-in fade-in duration-500">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Histórico de Compras</h1>
                <p class="text-gray-500 mt-1 text-base">Ordens organizadas por mês, como no protótipo original.</p>
            </div>

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
                                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $order['orderId'] }}</p>
                                            <p class="text-xs text-gray-500">Em {{ $order['date'] }} às {{ $order['time'] }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $order['status'] === 'entregue' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                            {{ $order['status'] === 'entregue' ? 'Entregue' : 'Aguardando' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @empty
                    <div class="bg-white rounded-3xl border border-gray-100 p-10 text-center text-gray-500">Nenhuma planilha de compra registrada no sistema.</div>
                @endforelse
            </div>
        </div>
    @elseif ($activeView === 'library')
        <div class="animate-in fade-in duration-500">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Acervo</h1>
                <p class="text-gray-500 mt-1 text-base">Catálogo base para navegação e futuras reservas/retiradas.</p>
            </div>

            <div class="space-y-10">
                @foreach ($booksArray->groupBy('subject') as $subject => $subjectBooks)
                    <section>
                        <div class="flex items-center justify-between px-2 mb-5">
                            <h3 class="text-xl font-semibold text-gray-900 tracking-tight">{{ $subject }}</h3>
                            <span class="text-sm font-medium text-gray-400">{{ $subjectBooks->count() }} itens</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($subjectBooks as $book)
                                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition-shadow">
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
                                    <div class="flex gap-2">
                                        <button type="button" @click="$dispatch('open-modal', 'receive-book-{{ $book['id'] }}')" class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-colors">
                                            ⬇ Entrada
                                        </button>
                                        <button type="button" @click="$dispatch('open-modal', 'withdraw-book-{{ $book['id'] }}')" class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-orange-50 hover:bg-orange-100 text-orange-600 rounded-lg text-xs font-medium transition-colors">
                                            ⬆ Saída
                                        </button>
                                    </div>
                                </div>

                                <!-- Modal Recebimento -->
                                <x-modal :name="'receive-book-' . $book['id']" :show="false" maxWidth="md">
                                    <div class="p-6">
                                        <div class="mb-6">
                                            <h2 class="text-xl font-semibold text-gray-900">Entrada de Estoque</h2>
                                            <p class="text-gray-600 text-sm mt-1">{{ $book['title'] }}</p>
                                        </div>

                                        <form x-data="{ submitting: false }" @submit.prevent="submitting = true; fetch('/api/books/{{ $book['id'] }}/receive', { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ quantity: this.$refs.qty.value, notes: this.$refs.notes.value }) }).then(r => r.json()).then(d => { alert(d.message); $dispatch('close-modal', 'receive-book-{{ $book['id'] }}'); location.reload(); }).catch(e => { alert('Erro: ' + e.message); submitting = false; })" class="space-y-4">
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

                                        <form x-data="{ submitting: false, qty: 0 }" @submit.prevent="submitting = true; fetch('/api/books/{{ $book['id'] }}/withdraw', { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ quantity: this.$refs.qty.value, justification: this.$refs.justif.value }) }).then(r => r.json()).then(d => { alert(d.message); $dispatch('close-modal', 'withdraw-book-{{ $book['id'] }}'); location.reload(); }).catch(e => { alert('Erro: ' + e.message); submitting = false; })" class="space-y-4">
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
    @elseif ($activeView === 'receive')
        <div class="animate-in fade-in duration-500 max-w-2xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Recebimento de Remessa</h1>
                <p class="text-gray-500 mt-1 text-base">Atualize o acervo com materiais já cadastrados ou novos títulos.</p>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8" x-data="{ mode: 'existing' }">
                <div class="flex p-1 bg-gray-100 rounded-xl mb-8">
                    <button type="button" @click="mode = 'existing'" class="flex-1 py-3 text-sm font-medium rounded-lg" :class="mode === 'existing' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Material Existente</button>
                    <button type="button" @click="mode = 'new'" class="flex-1 py-3 text-sm font-medium rounded-lg" :class="mode === 'new' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'">Cadastrar Novo Material</button>
                </div>

                <div x-show="mode === 'existing'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Livro Recebido</label>
                        <select class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none">
                            <option>Selecione um título do acervo...</option>
                            @foreach ($booksArray as $book)
                                <option>{{ $book['title'] }} (Atual: {{ $book['quantity'] }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Quantidade Recebida</label>
                        <input type="number" min="1" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: 50">
                    </div>

                    <button type="button" class="w-full rounded-xl bg-emerald-600 px-4 py-4 font-medium text-white hover:bg-emerald-700 transition-colors">Confirmar Recebimento de Estoque</button>
                </div>

                <div x-show="mode === 'new'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Título do Novo Livro</label>
                        <input type="text" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: Introdução à Robótica">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Área / Matéria</label>
                            <input type="text" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: Mecatrônica">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">ISBN (Opcional)</label>
                            <input type="text" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: 978-85-...">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Descrição Curta</label>
                        <textarea rows="2" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none resize-none" placeholder="Breve resumo sobre o conteúdo do material..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Quantidade Recebida na Remessa</label>
                        <input type="number" min="1" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: 100">
                    </div>

                    <button type="button" class="w-full rounded-xl bg-emerald-600 px-4 py-4 font-medium text-white hover:bg-emerald-700 transition-colors">Cadastrar e Receber Estoque</button>
                </div>
            </div>
        </div>
    @elseif ($activeView === 'withdraw')
        <div class="animate-in fade-in duration-500 max-w-3xl mx-auto" x-data='withdrawCartForm(@js($booksArray), @js($turmaOptions))'>
            <div class="mb-8">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Retirada em Lote</h1>
                <p class="text-gray-500 mt-1 text-base">O campo de destino agora usa as turmas reais do banco de dados.</p>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8" x-init="init()">
                <div class="space-y-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Turma / Destino Final</label>
                        <select x-model="destination" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none">
                            <option value="">Selecione uma turma...</option>
                            <template x-for="turma in turmas" :key="turma.id">
                                <option :value="turma.nome_turma" x-text="turma.nome_turma"></option>
                            </template>
                        </select>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-900">Itens para Retirada</label>
                            <span class="text-xs text-gray-400 font-medium" x-text="`${rows.length} linha(s)`"></span>
                        </div>

                        <template x-for="row in rows" :key="row.id">
                            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                                <div class="flex-1 w-full">
                                    <select x-model="row.bookId" @change="sanitizeQuantity(row)" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 h-[52px] text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none">
                                        <option value="">Selecione um título...</option>
                                        <template x-for="book in catalog" :key="book.id">
                                            <option :value="book.id" x-text="`${book.title} • ${book.quantity} un`"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <div class="h-[52px] w-full sm:w-20 flex flex-col items-center justify-center bg-gray-100/80 border border-gray-100 rounded-xl text-sm shrink-0">
                                        <span class="text-[10px] uppercase font-semibold text-gray-500 leading-none mb-1">Max</span>
                                        <span class="font-bold leading-none text-gray-900" x-text="maxAllowed(row) ?? '-' "></span>
                                    </div>

                                    <input type="number" min="1" :max="maxAllowed(row)" x-model.number="row.quantity" @input="sanitizeQuantity(row)" class="h-[52px] w-full sm:w-24 px-4 bg-gray-50 border border-gray-100 rounded-xl text-gray-900 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Qtd.">

                                    <button type="button" @click="removeRow(row.id)" class="h-[52px] w-[52px] flex items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-600 hover:bg-red-100 transition-all">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addRow()" class="inline-flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Adicionar outro título</button>
                    </div>

                    <div class="bg-orange-50/50 rounded-xl p-4 flex items-start border border-orange-100/50">
                        <p class="text-sm text-gray-600 leading-relaxed">Verifique se o saldo é suficiente. O sistema bloqueia a retirada quando o estoque disponível é atingido.</p>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <button type="button" class="w-full font-medium py-4 px-4 rounded-xl transition-colors flex items-center justify-center bg-red-600 hover:bg-red-700 text-white">Registrar Lote de Retirada</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
