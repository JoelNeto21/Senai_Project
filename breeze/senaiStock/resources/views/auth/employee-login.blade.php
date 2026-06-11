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
    <body class="font-sans antialiased bg-[#F5F5F7] text-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center p-6">
            <div class="w-full max-w-sm">
                <div class="text-center mb-8">
                    <div class="w-14 h-14 bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <span class="text-white text-2xl font-bold">S</span>
                    </div>
                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Acesso Interno</h1>
                    <p class="text-gray-500 mt-1 text-sm">Gestão de livros SenaiStock</p>
                </div>

                <form method="POST" action="{{ route('employee.authenticate') }}" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 space-y-6">
                    @csrf

                    @if (session('status'))
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-900 mb-2">NIF do Funcionário</label>
                        <input
                            id="nif"
                            type="text"
                            name="nif"
                            value="{{ old('nif') }}"
                            required
                            autofocus
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-red-500 outline-none"
                            placeholder="Ex: 111111"
                        >
                        @error('nif')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-900 mb-2">Senha</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-red-500 outline-none"
                            placeholder="Digite sua senha"
                        >
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-gray-900 px-4 py-3.5 text-sm font-semibold text-white hover:bg-gray-800 transition-colors">
                        Entrar
                    </button>

                    <p class="hidden">
                        Todos os funcionários cadastrados podem entrar com seu perfil.
                    </p>
                    <p class="text-sm text-center text-gray-500">
                        Professor sem conta?
                        <a href="{{ route('employee.register') }}" class="font-semibold text-gray-900 hover:text-red-600">Cadastre-se</a>
                    </p>
                </form>
            </div>
        </div>
    </body>
</html>
