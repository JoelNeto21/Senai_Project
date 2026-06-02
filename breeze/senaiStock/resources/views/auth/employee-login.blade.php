<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Entrar | SenaiStock</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-[#f5f5f7] font-sans text-gray-950 antialiased">
        <main class="relative min-h-screen px-5 py-8 sm:px-8">
            <div class="absolute inset-x-0 top-0 h-56 bg-gradient-to-b from-white to-transparent"></div>
            <div class="relative mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl items-center gap-10 lg:grid-cols-12">
                <section class="lg:col-span-6">
                    <div class="mb-10 inline-flex items-center gap-3 rounded-full border border-white bg-white/80 px-4 py-2 shadow-sm backdrop-blur">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                        <span class="text-xs font-bold uppercase tracking-[0.18em] text-gray-600">SENAI-SP</span>
                    </div>
                    <h1 class="max-w-xl text-5xl font-semibold tracking-tight text-gray-950 sm:text-6xl">SenaiStock</h1>
                    <p class="mt-5 max-w-xl text-lg leading-8 text-gray-600">Controle profissional de livros didáticos para almoxarifado, turmas e reposição preventiva.</p>
                    <div class="mt-8 grid max-w-xl grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-3xl border border-white bg-white/75 p-4 shadow-sm backdrop-blur">
                            <p class="text-2xl font-semibold text-gray-950">Saldo</p>
                            <p class="mt-1 text-sm text-gray-500">real por livro</p>
                        </div>
                        <div class="rounded-3xl border border-white bg-white/75 p-4 shadow-sm backdrop-blur">
                            <p class="text-2xl font-semibold text-gray-950">Alertas</p>
                            <p class="mt-1 text-sm text-gray-500">estoque mínimo</p>
                        </div>
                        <div class="rounded-3xl border border-white bg-white/75 p-4 shadow-sm backdrop-blur">
                            <p class="text-2xl font-semibold text-gray-950">Pedidos</p>
                            <p class="mt-1 text-sm text-gray-500">professores</p>
                        </div>
                    </div>
                    <a href="{{ route('teacher-requests.create') }}" class="mt-8 inline-flex rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-800 shadow-sm transition hover:bg-gray-50">Área pública do professor</a>
                </section>

                <section class="lg:col-span-6">
                    <div class="mx-auto w-full max-w-md rounded-[2rem] border border-white bg-white p-7 shadow-[0_30px_100px_rgba(17,24,39,0.10)] sm:p-9" x-data>
                        <div class="mb-8">
                            <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-3xl bg-red-600 text-xl font-bold text-white shadow-sm">S</div>
                            <h2 class="text-3xl font-semibold tracking-tight text-gray-950">Acesso interno</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500">Entre com NIF e CPF para acessar o painel do almoxarifado.</p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('employee.authenticate') }}" class="space-y-5">
                            @csrf
                            <div>
                                <label for="nif" class="mb-2 block text-sm font-semibold text-gray-900">NIF</label>
                                <input id="nif" name="nif" type="number" value="{{ old('nif') }}" required class="senai-input" placeholder="Ex: 123456" x-ref="nif">
                            </div>

                            <div>
                                <label for="cpf" class="mb-2 block text-sm font-semibold text-gray-900">CPF</label>
                                <input id="cpf" name="cpf" type="text" value="{{ old('cpf') }}" required class="senai-input" placeholder="00000000000" x-ref="cpf">
                            </div>

                            <button type="submit" class="w-full rounded-2xl bg-gray-950 px-4 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                                Entrar no painel
                            </button>
                        </form>
                    </div>
                </section>
            </div>

            <aside
                x-data="demoAccessCard()"
                class="fixed bottom-5 right-5 z-50 w-[calc(100vw-2.5rem)] max-w-[18rem]"
            >
                <button
                    type="button"
                    @click="open = !open"
                    class="ml-auto flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 shadow-[0_16px_45px_rgba(17,24,39,0.14)] transition hover:bg-gray-50"
                    :aria-expanded="open.toString()"
                >
                    <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                    Usuários de teste
                </button>

                <div x-show="open" x-transition.origin.bottom.right x-cloak class="mt-2 rounded-[1.25rem] border border-white bg-white p-3 shadow-[0_24px_80px_rgba(17,24,39,0.16)]">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-sm font-bold text-gray-950">Preenchimento rápido</p>
                        <button type="button" @click="open = false" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700" aria-label="Recolher card">
                            <span aria-hidden="true">x</span>
                        </button>
                    </div>
                    <div class="space-y-1.5">
                        <template x-for="user in users" :key="user.label">
                            <button
                                type="button"
                                @click="fill(user)"
                                class="flex w-full items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-left transition hover:border-red-100 hover:bg-red-50"
                            >
                                <span>
                                    <span class="block text-sm font-semibold text-gray-950" x-text="user.label"></span>
                                    <span class="block text-xs text-gray-500" x-text="user.role"></span>
                                </span>
                                <span class="text-xs font-bold text-red-600">Usar</span>
                            </button>
                        </template>
                    </div>
                    <a href="{{ route('teacher-requests.create') }}" class="mt-2 block rounded-xl bg-gray-950 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-red-700">Fluxo do professor</a>
                </div>
            </aside>
        </main>
    </body>
</html>
