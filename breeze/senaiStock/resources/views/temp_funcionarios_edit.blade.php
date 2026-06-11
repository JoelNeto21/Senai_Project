<x-app-layout
    :active-view="'funcionarios'"
    :navigation-items="[]"
    :employee="[]"
>
    <div class="animate-in fade-in duration-500 max-w-2xl mx-auto">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Editar funcionário</h1>
                <p class="text-gray-500 mt-1 text-base">Atualize o nome, NIF ou tipo de acesso.</p>
            </div>
            <button type="button" onclick="history.back()" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Voltar</button>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('funcionarios.update', $funcionario->Id_funcionario) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="Nome" value="Nome Completo" />
                    <x-text-input
                        id="Nome"
                        class="mt-2 w-full"
                        type="text"
                        name="Nome"
                        value="{{ old('Nome', $funcionario->Nome) }}"
                        required
                        autofocus
                        placeholder="Ex: João Silva"
                    />
                    @error('Nome')
                        <x-input-error :messages="$errors->get('Nome')" class="mt-2" />
                    @enderror
                </div>

                <div>
                    <x-input-label for="NIF" value="NIF" />
                    <x-text-input
                        id="NIF"
                        class="mt-2 w-full"
                        type="number"
                        name="NIF"
                        value="{{ old('NIF', $funcionario->NIF) }}"
                        required
                        placeholder="Ex: 123456"
                    />
                    @error('NIF')
                        <x-input-error :messages="$errors->get('NIF')" class="mt-2" />
                    @enderror
                </div>

                <div>
                    <x-input-label for="Id_cargo_FK" value="Cargo" />
                    <select
                        id="Id_cargo_FK"
                        name="Id_cargo_FK"
                        class="mt-2 w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none"
                        required
                    >
                        <option value="">Selecione um cargo...</option>
                        @foreach ($cargos as $cargo)
                            <option value="{{ $cargo->Id_cargo }}" {{ old('Id_cargo_FK', $funcionario->Id_cargo_FK) == $cargo->Id_cargo ? 'selected' : '' }}>
                                {{ $cargo->Nome_cargo }}
                            </option>
                        @endforeach
                    </select>
                    @error('Id_cargo_FK')
                        <x-input-error :messages="$errors->get('Id_cargo_FK')" class="mt-2" />
                    @enderror
                </div>

                <div class="flex gap-3 pt-6">
                    <button type="submit" class="rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">
                        Atualizar
                    </button>
                    <a href="{{ route('senai.dashboard', ['view' => 'people']) }}" class="rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
