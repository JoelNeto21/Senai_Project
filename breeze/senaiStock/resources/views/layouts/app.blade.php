@props([
    'activeView' => 'insights',
    'navigationItems' => [],
    'employee' => [],
    'purchaseCartCount' => 0,
    'withdrawCartCount' => 0,
    'pendingTeacherRequests' => 0,
    'alertCount' => 0,
    'supplierCount' => 0,
    'searchBooks' => [],
])

@php
    $employeeName = data_get($employee, 'name', 'Funcionário');
    $employeeCargo = data_get($employee, 'cargo', 'Sem cargo definido');
    $nameParts = collect(preg_split('/\s+/', trim($employeeName)))->filter();
    $employeeInitials = $nameParts->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    $navigationItems = count($navigationItems) > 0 ? $navigationItems : config('senaistock.navigation_items', []);
    $groupedNavigationItems = collect($navigationItems)->groupBy(fn ($item) => $item['group'] ?? 'Menu');
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SenaiStock') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F5F5F7] text-gray-900">
        <div x-data="{ mobileMenuOpen: false, passwordOpen: @js($errors->has('current_password') || $errors->has('password')) }" class="min-h-screen flex flex-col md:flex-row selection:bg-red-100 selection:text-red-900">
            <header class="md:hidden bg-white/85 backdrop-blur-md sticky top-0 border-b border-gray-100 px-4 py-3 flex items-center justify-between z-30">
                <div class="flex items-center font-semibold text-gray-900 tracking-tight">
                    <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center mr-2">
                        <span class="text-white text-sm font-bold">S</span>
                    </div>
                    SenaiStock
                </div>
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-gray-600 rounded-xl hover:bg-gray-100">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 md:hidden" @click="mobileMenuOpen = false"></div>

            <aside class="fixed md:sticky top-0 left-0 h-screen w-72 bg-white/95 backdrop-blur-xl border-r border-gray-200 z-50 transform transition-transform duration-300 ease-out md:translate-x-0 flex flex-col shadow-[8px_0_30px_rgba(17,24,39,0.03)]" :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
                <div class="p-8 flex items-center">
                    <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                        <span class="text-white text-sm font-bold">S</span>
                    </div>
                    <span class="text-xl font-semibold text-gray-900 tracking-tight">SenaiStock</span>
                </div>

                <div data-navigation-scroll-container class="px-4 flex-1 overflow-y-auto pb-4">
                    <nav class="space-y-6">
                        @foreach ($groupedNavigationItems as $group => $items)
                            <div>
                                <p class="px-4 pb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">{{ $group }}</p>
                                <div class="space-y-1">
                                    @foreach ($items as $item)
                                        @php
                                            $isActive = $activeView === $item['id'];
                                            $badgeCount = match ($item['id']) {
                                                'alerts' => $alertCount,
                                                'teacher_requests' => $pendingTeacherRequests,
                                                'purchases' => $purchaseCartCount,
                                                'stock' => $withdrawCartCount,
                                                default => 0,
                                            };
                                            $icon = $item['icon'] ?? strtoupper(mb_substr($item['label'], 0, 1));
                                        @endphp

                                        <a
                                            href="{{ route('senai.dashboard', ['view' => $item['id']]) }}"
                                            data-preserve-scroll
                                            class="relative w-full flex items-center justify-between px-3 py-2.5 rounded-2xl transition-all duration-200 {{ $isActive ? 'bg-red-50/70 text-gray-900 font-semibold shadow-sm before:absolute before:left-0 before:h-5 before:w-1 before:rounded-full before:bg-red-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}"
                                        >
                                            <span class="flex items-center min-w-0">
                                                <span class="w-8 h-8 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center mr-3 text-[11px] font-bold tracking-wide shrink-0 {{ $isActive ? 'bg-red-50 text-red-600' : '' }}">
                                                    {{ $icon }}
                                                </span>
                                                <span class="truncate text-sm">{{ $item['label'] }}</span>
                                            </span>
                                            @if ($badgeCount > 0)
                                                <span class="ml-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $badgeCount }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </div>

                <div class="p-4 mt-auto">
                    <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-gray-50 to-white p-4 mb-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-gray-900 text-white flex items-center justify-center font-semibold">
                                {{ $employeeInitials ?: 'S' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $employeeName }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $employeeCargo }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="passwordOpen = true" class="flex items-center justify-center rounded-xl px-3 py-3 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900">
                            Alterar senha
                        </button>
                        <form method="POST" action="{{ route('employee.logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center px-3 py-3 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">
                                Encerrar sessão
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <main class="flex-1 p-6 sm:p-10 lg:p-12 max-w-6xl mx-auto w-full overflow-x-hidden">
                @if (count($navigationItems) > 0)
                    <div
                        class="sticky top-20 z-30 mb-8 md:top-3 md:flex md:justify-end"
                        x-data="spotlightSearch(@js(collect($searchBooks)->values()), @js(collect($navigationItems)->values()), @js(route('senai.dashboard', ['view' => '__VIEW__'])))"
                        @keydown.escape.window="query = ''"
                    >
                        <div class="relative w-full md:w-[26rem]">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                            <input
                                type="search"
                                x-model="query"
                                class="w-full rounded-2xl border border-gray-200 bg-white/95 py-3.5 pl-12 pr-4 text-sm text-gray-900 shadow-sm backdrop-blur-xl outline-none transition focus:border-red-200 focus:ring-4 focus:ring-red-50"
                                placeholder="Buscar livros ou áreas do sistema..."
                                aria-label="Busca rápida do sistema"
                                autocomplete="off"
                            >
                            <div x-show="results.length > 0" x-cloak @click.outside="query = ''" class="absolute left-0 right-0 top-[calc(100%+0.5rem)] overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl">
                                <template x-for="result in results" :key="`${result.type}-${result.title}`">
                                    <a :href="result.url" class="flex items-center justify-between gap-4 border-b border-gray-50 px-5 py-3.5 last:border-b-0 hover:bg-gray-50">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900" x-text="result.title"></p>
                                            <p class="truncate text-xs text-gray-500" x-text="result.subtitle"></p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-gray-100 px-3 py-1 text-[10px] font-bold uppercase text-gray-500" x-text="result.type"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('status'))
                    <div class="senai-feedback mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 before:bg-emerald-500">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="senai-feedback mb-6 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-medium text-red-700 before:bg-red-500">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{ $slot }}
            </main>

            <div x-show="passwordOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/30 p-4 backdrop-blur-sm" @keydown.escape.window="passwordOpen = false">
                <div class="w-full max-w-md rounded-3xl border border-gray-100 bg-white p-6 shadow-xl" @click.outside="passwordOpen = false">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Alterar senha</h2>
                            <p class="mt-1 text-sm text-gray-500">Confirme sua senha atual e escolha uma nova.</p>
                        </div>
                        <button type="button" @click="passwordOpen = false" class="rounded-lg px-2 py-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700">Fechar</button>
                    </div>
                    <form method="POST" action="{{ route('employee.password.update') }}" class="mt-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="current_password" class="mb-2 block text-sm font-medium text-gray-900">Senha atual</label>
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500">
                            @error('current_password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="new_employee_password" class="mb-2 block text-sm font-medium text-gray-900">Nova senha</label>
                            <input id="new_employee_password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500">
                            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="new_employee_password_confirmation" class="mb-2 block text-sm font-medium text-gray-900">Confirmar nova senha</label>
                            <input id="new_employee_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500">
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-800">Salvar nova senha</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
