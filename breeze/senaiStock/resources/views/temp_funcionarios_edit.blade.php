<x-app-layout
    :active-view="'funcionarios'"
    :navigation-items="[]"
    :employee="[]"
>
    <div class="animate-in fade-in duration-500 max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Editar Funcionário</h1>
            <p class="text-gray-500 mt-1 text-base">Atualize as informações do colaborador.</p>
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
                    <x-input-label for="Cpf" value="CPF" />
                    <x-text-input
                        id="Cpf"
                        class="mt-2 w-full"
                        type="text"
                        name="Cpf"
                        value="{{ old('Cpf', $funcionario->Cpf) }}"
                        required
                        placeholder="Ex: 123.456.789-10"
                    />
                    @error('Cpf')
                        <x-input-error :messages="$errors->get('Cpf')" class="mt-2" />
                    @enderror
                </div>

                <div>
                    <x-input-label for="Id_cargo_FK" value="Cargo" />
                    <select
                        id="Id_cargo_FK"
                        name="Id_cargo_FK"
                        class="mt-2 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
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
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Atualizar
                    </button>
                    <a href="{{ route('funcionarios.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
