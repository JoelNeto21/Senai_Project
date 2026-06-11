<x-app-layout
    :active-view="'cargos'"
    :navigation-items="[]"
    :employee="[]"
>
    <div class="animate-in fade-in duration-500">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Cargos</h1>
                <p class="text-gray-500 mt-1 text-base">Cargos fixos disponíveis no sistema.</p>
            </div>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cargos as $cargo)
                            <tr class="border-t border-gray-50 hover:bg-gray-50/60 transition-colors {{ $loop->odd ? 'bg-white' : 'bg-gray-50/30' }}">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $cargo->Nome_cargo }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-10 text-center text-gray-500">
                                    Nenhum cargo cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
