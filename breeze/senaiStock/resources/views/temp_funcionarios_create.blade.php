<x-app-layout
    :active-view="'funcionarios'"
    :navigation-items="[]"
    :employee="[]"
>
    <div class="animate-in fade-in duration-500 max-w-3xl mx-auto">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Novo funcionário</h1>
                <p class="text-gray-500 mt-1 text-base">Cadastre o acesso de um Coordenador ou Professor.</p>
            </div>
            <button type="button" onclick="history.back()" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Voltar</button>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('funcionarios.store') }}" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <label for="Nome" class="block text-sm font-medium text-gray-900 mb-2">Nome completo</label>
                    <input id="Nome" name="Nome" value="{{ old('Nome') }}" required autofocus class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: João Silva">
                    @error('Nome') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="NIF" class="block text-sm font-medium text-gray-900 mb-2">NIF</label>
                    <input id="NIF" type="number" name="NIF" value="{{ old('NIF') }}" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Ex: 123456">
                    @error('NIF') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="Id_cargo_FK" class="block text-sm font-medium text-gray-900 mb-2">Tipo de acesso</label>
                    <select id="Id_cargo_FK" name="Id_cargo_FK" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-red-500 outline-none">
                        <option value="">Selecione...</option>
                        @foreach ($cargos as $cargo)
                            <option value="{{ $cargo->Id_cargo }}" {{ old('Id_cargo_FK') == $cargo->Id_cargo ? 'selected' : '' }}>
                                {{ $cargo->Nome_cargo }}
                            </option>
                        @endforeach
                    </select>
                    @error('Id_cargo_FK') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                    A senha inicial será <strong>senai123</strong>.
                </div>

                <div class="md:col-span-2 flex gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">Cadastrar funcionário</button>
                    <a href="{{ route('senai.dashboard', ['view' => 'people']) }}" class="rounded-xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
