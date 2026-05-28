<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Acesso restrito | SenaiStock</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f5f7] text-gray-950 antialiased">
        @php
            $employee = session('employee');
        @endphp

        <main class="min-h-screen px-6 py-8 sm:px-10">
            <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-5xl flex-col">
                <header class="flex items-center justify-between gap-4">
                    <a href="{{ route('employee.login') }}" class="inline-flex items-center gap-3 text-sm font-semibold text-gray-900">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-red-600 text-sm font-bold text-white shadow-sm">S</span>
                        SenaiStock
                    </a>
                    @if ($employee)
                        <a href="{{ route('senai.dashboard', ['view' => 'insights']) }}" class="rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Voltar ao painel</a>
                    @endif
                </header>

                <section class="grid flex-1 place-items-center py-16">
                    <div class="w-full max-w-2xl rounded-[32px] border border-gray-100 bg-white p-8 text-center shadow-[0_28px_80px_rgba(15,23,42,0.10)] sm:p-12">
                        <p class="mx-auto inline-flex rounded-full border border-red-100 bg-red-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-red-600">403</p>
                        <h1 class="mt-6 text-3xl font-semibold tracking-tight text-gray-950 sm:text-5xl">Acesso restrito</h1>
                        <p class="mx-auto mt-4 max-w-xl text-base leading-7 text-gray-600">
                            Esta área é reservada a perfis administrativos. Use o menu permitido para continuar a operação do almoxarifado.
                        </p>
                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            @if ($employee)
                                <a href="{{ route('senai.dashboard', ['view' => 'insights']) }}" class="rounded-2xl bg-gray-950 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-gray-800">Ir para insights</a>
                            @else
                                <a href="{{ route('employee.login') }}" class="rounded-2xl bg-gray-950 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-gray-800">Entrar</a>
                            @endif
                            <a href="{{ route('teacher-requests.create') }}" class="rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50">Área pública</a>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
