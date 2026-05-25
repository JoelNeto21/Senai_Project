<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Entrar | SenaiStock</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F5F5F7]">
        <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
            <div class="w-full max-w-md bg-white rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 sm:p-10">
                <div class="text-center mb-8">
                    <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-red-600 text-white mx-auto mb-5 shadow-sm">
                        <span class="text-xl font-bold">S</span>
                    </div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900">SenaiStock</h1>
                    <p class="mt-2 text-sm text-gray-500">Entre com o NIF e o CPF do funcionário para acessar o painel.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('employee.authenticate') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="nif" class="mb-2 block text-sm font-medium text-gray-900">NIF</label>
                        <input
                            id="nif"
                            name="nif"
                            type="number"
                            value="{{ old('nif', 123456) }}"
                            required
                            class="w-full rounded-xl border-0 bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none transition-all"
                            placeholder="Ex: 123456"
                        >
                    </div>

                    <div>
                        <label for="cpf" class="mb-2 block text-sm font-medium text-gray-900">CPF</label>
                        <input
                            id="cpf"
                            name="cpf"
                            type="text"
                            value="{{ old('cpf', '12345678900') }}"
                            required
                            class="w-full rounded-xl border-0 bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none transition-all"
                            placeholder="Ex: 000.000.000-00"
                        >
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-gray-900 px-4 py-3.5 font-medium text-white transition-colors hover:bg-gray-800">
                        Continuar
                    </button>
                </form>

                <p class="mt-6 text-center text-xs leading-relaxed text-gray-400">
                    Nesta etapa o acesso usa os dados já existentes na tabela de Funcionários.
                </p>
            </div>
        </div>
    </body>
</html>
