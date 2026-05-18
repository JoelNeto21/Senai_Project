<x-app-layout
    :active-view="'cargos'"
    :navigation-items="[]"
    :employee="[]"
>
    <div class="animate-in fade-in duration-500">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Cargos</h1>
                <p class="text-gray-500 mt-1 text-base">Gerenciamento de cargos e posições no sistema.</p>
            </div>
            <button type="button" @click="$dispatch('open-modal', 'novo-cargo')" class="inline-flex items-center px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-sm font-medium transition-colors">
                <span class="mr-2">+</span>
                Novo Cargo
            </button>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-medium flex items-center gap-3">
                <span>✓</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50/80 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Nome do Cargo</th>
                            <th class="px-6 py-4 font-medium text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cargos as $cargo)
                            <tr class="border-t border-gray-50 hover:bg-gray-50/60 transition-colors {{ $loop->odd ? 'bg-white' : 'bg-gray-50/30' }}">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $cargo->Nome_cargo }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('cargos.destroy', $cargo->Id_cargo) }}" class="inline" x-data @submit.prevent="if (confirm('Tem certeza que deseja deletar este cargo?')) { $el.submit(); }">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                            Deletar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-10 text-center text-gray-500">
                                    Nenhum cargo cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para novo cargo -->
        <x-modal name="novo-cargo" :show="false" maxWidth="md">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Novo Cargo</h2>
                </div>

                <form method="POST" action="{{ route('cargos.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="Nome_cargo" value="Nome do Cargo" />
                        <x-text-input
                            id="Nome_cargo"
                            class="mt-2 w-full"
                            type="text"
                            name="Nome_cargo"
                            value="{{ old('Nome_cargo') }}"
                            required
                            autofocus
                            placeholder="Ex: Gerenciador de Estoque"
                        />
                        @error('Nome_cargo')
                            <x-input-error :messages="$errors->get('Nome_cargo')" class="mt-2" />
                        @enderror
                    </div>

                    <div class="flex gap-3 justify-end mt-6">
                        <button type="button" @click="$dispatch('close-modal', 'novo-cargo')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Salvar Cargo
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>
</x-app-layout>
