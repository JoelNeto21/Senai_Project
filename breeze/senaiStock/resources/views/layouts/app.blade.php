@props([
    'activeView' => 'insights',
    'navigationItems' => [],
    'employee' => [],
    'purchaseCartCount' => 0,
    'withdrawCartCount' => 0,
    'pendingTeacherRequests' => 0,
    'alertCount' => 0,
    'supplierCount' => 0,
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
        <div x-data="{ mobileMenuOpen: false }" class="min-h-screen flex flex-col md:flex-row selection:bg-red-100 selection:text-red-900">
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

            <aside class="fixed md:sticky top-0 left-0 h-screen w-72 bg-white border-r border-gray-200 z-50 transform transition-transform duration-300 ease-out md:translate-x-0 flex flex-col" :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
                <div class="p-8 flex items-center">
                    <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center mr-3 shadow-sm">
                        <span class="text-white text-sm font-bold">S</span>
                    </div>
                    <span class="text-xl font-semibold text-gray-900 tracking-tight">SenaiStock</span>
                </div>

                <div class="px-4 flex-1 overflow-y-auto pb-4">
                    <nav class="space-y-5">
                        @foreach ($groupedNavigationItems as $group => $items)
                            <div>
                                <p class="px-4 pb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-gray-400">{{ $group }}</p>
                                <div class="space-y-1">
                                    @foreach ($items as $item)
                                        @php
                                            $isActive = $activeView === $item['id'];
                                            $badgeCount = match ($item['id']) {
                                                'alerts' => $alertCount,
                                                'teacher_requests' => $pendingTeacherRequests,
                                                'purchases' => $purchaseCartCount,
                                                'withdraw' => $withdrawCartCount,
                                                default => 0,
                                            };
                                            $icon = $item['icon'] ?? strtoupper(mb_substr($item['label'], 0, 1));
                                        @endphp

                                        <a
                                            href="{{ route('senai.dashboard', ['view' => $item['id']]) }}"
                                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-2xl transition-all duration-200 {{ $isActive ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}"
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
                    <div class="bg-gray-50 rounded-2xl p-4 mb-3">
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
                    <form method="POST" action="{{ route('employee.logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">
                            <span class="mr-2">⎋</span>
                            Encerrar Sessão
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 p-6 sm:p-10 lg:p-12 max-w-6xl mx-auto w-full overflow-x-hidden">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
