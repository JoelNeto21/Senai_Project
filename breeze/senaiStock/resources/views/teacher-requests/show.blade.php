<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Protocolo {{ $teacherRequest->protocol }} | SenaiStock</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f5f7] font-sans text-gray-950 antialiased">
        @php
            $statusMeta = [
                'pendente' => ['label' => 'Pendente', 'class' => 'bg-amber-50 text-amber-700 border-amber-100'],
                'aprovado' => ['label' => 'Aprovado', 'class' => 'bg-blue-50 text-blue-700 border-blue-100'],
                'separado' => ['label' => 'Separado', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100'],
                'atendido' => ['label' => 'Atendido', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100'],
                'rejeitado' => ['label' => 'Rejeitado', 'class' => 'bg-red-50 text-red-700 border-red-100'],
                'compra' => ['label' => 'Em compra', 'class' => 'bg-purple-50 text-purple-700 border-purple-100'],
            ];
            $currentStatus = $statusMeta[$teacherRequest->status] ?? ['label' => ucfirst($teacherRequest->status), 'class' => 'bg-gray-50 text-gray-700 border-gray-100'];
        @endphp

        <main class="mx-auto max-w-5xl px-6 py-8 sm:py-12">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('teacher-requests.create') }}" class="inline-flex items-center gap-3 text-sm font-semibold text-gray-900">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-600 text-white">S</span>
                    SenaiStock
                </a>
                <a href="{{ route('teacher-requests.create') }}" class="rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Nova solicitação</a>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="rounded-[2rem] border border-white bg-white p-6 shadow-[0_24px_80px_rgba(17,24,39,0.08)] sm:p-8">
                <div class="flex flex-col gap-5 border-b border-gray-100 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-red-600">Protocolo</p>
                        <h1 class="text-3xl font-semibold tracking-tight text-gray-950">{{ $teacherRequest->protocol }}</h1>
                        <p class="mt-2 text-sm text-gray-500">Solicitado em {{ $teacherRequest->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full border px-4 py-2 text-xs font-bold uppercase tracking-wide {{ $currentStatus['class'] }}">
                        {{ $currentStatus['label'] }}
                    </span>
                </div>

                <div class="grid gap-4 py-6 md:grid-cols-4">
                    <div class="rounded-3xl bg-gray-50 p-4 md:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Livro</p>
                        <p class="mt-2 font-semibold text-gray-950">{{ $teacherRequest->title }}</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Quantidade</p>
                        <p class="mt-2 font-semibold text-gray-950">{{ $teacherRequest->quantity }} un</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Turma</p>
                        <p class="mt-2 font-semibold text-gray-950">{{ $teacherRequest->class_name }}</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 p-4 md:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Curso</p>
                        <p class="mt-2 font-semibold text-gray-950">{{ $teacherRequest->course_name ?: 'Nao informado' }}</p>
                    </div>
                    <div class="rounded-3xl bg-gray-50 p-4 md:col-span-2">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Professor</p>
                        <p class="mt-2 font-semibold text-gray-950">{{ $teacherRequest->teacher_name }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h2 class="text-lg font-semibold text-gray-950">Histórico de comunicação</h2>
                    <div class="mt-5 space-y-3">
                        @forelse ($teacherRequest->messages as $message)
                            <div class="rounded-3xl border border-gray-100 bg-gray-50/70 p-4">
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-gray-950">{{ $message->sender_name ?: ucfirst($message->sender_type) }}</span>
                                    <span class="text-xs text-gray-400">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-sm leading-6 text-gray-600">{{ $message->message }}</p>
                            </div>
                        @empty
                            <p class="rounded-3xl border border-gray-100 bg-gray-50 p-5 text-sm text-gray-500">Nenhuma mensagem registrada ainda.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
