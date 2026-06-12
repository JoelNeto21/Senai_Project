<x-app-layout
    :active-view="'funcionarios'"
    :navigation-items="[]"
    :employee="[]"
>
    <div class="animate-in fade-in duration-500">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Funcionários</h1>
                <p class="text-gray-500 mt-1 text-base">Gerenciamento de colaboradores cadastrados no sistema.</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="history.back()" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Voltar</button>
                <a href="{{ route('funcionarios.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-medium transition-colors">Novo Funcionário</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-3">
                <span>✓</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table
                    class="w-full text-left text-sm whitespace-nowrap"
                    data-table-search-placeholder="Buscar funcionário"
                    data-table-filter-column="1"
                    data-table-filter-label="Todos os cargos"
                >
                    <thead class="bg-gray-50/80 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Nome</th>
                            <th class="px-6 py-4 font-medium">Cargo</th>
                            <th class="px-6 py-4 font-medium text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($funcionarios as $funcionario)
                            <tr class="border-t border-gray-50 hover:bg-gray-50/60 transition-colors {{ $loop->odd ? 'bg-white' : 'bg-gray-50/30' }}">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $funcionario->Nome }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
                                        {{ $funcionario->cargo->Nome_cargo ?? 'Sem cargo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('funcionarios.edit', $funcionario->Id_funcionario) }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('funcionarios.destroy', $funcionario->Id_funcionario) }}" class="inline" x-data @submit.prevent="if (confirm('Tem certeza que deseja deletar este funcionário?')) { $el.submit(); }">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                                Deletar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    Nenhum funcionário cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
