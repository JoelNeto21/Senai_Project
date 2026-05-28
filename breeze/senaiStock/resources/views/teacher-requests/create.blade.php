<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Solicitar livros | SenaiStock</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f5f7] font-sans text-gray-950 antialiased">
        <main class="mx-auto grid min-h-screen w-full max-w-7xl grid-cols-1 lg:grid-cols-12">
            <section class="flex flex-col justify-between px-6 py-8 sm:px-10 lg:col-span-5 lg:px-14 lg:py-14">
                <a href="{{ route('employee.login') }}" class="inline-flex w-fit items-center gap-3 text-sm font-semibold text-gray-900">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-600 text-white shadow-sm">S</span>
                    SenaiStock
                </a>

                <div class="my-12">
                    <p class="mb-4 inline-flex rounded-full border border-red-100 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-red-600 shadow-sm">Área pública</p>
                    <h1 class="max-w-xl text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">Solicite livros didáticos sem login.</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-gray-600">O pedido entra diretamente na fila do almoxarifado com protocolo de acompanhamento e notificações por e-mail.</p>
                </div>

                <div class="grid gap-3 text-sm text-gray-600 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-3xl border border-white bg-white/70 p-4 shadow-sm">
                        <p class="font-semibold text-gray-950">1. Solicitação</p>
                        <p class="mt-1">Informe turma, curso e material.</p>
                    </div>
                    <div class="rounded-3xl border border-white bg-white/70 p-4 shadow-sm">
                        <p class="font-semibold text-gray-950">2. Almoxarifado</p>
                        <p class="mt-1">A equipe valida saldo e separação.</p>
                    </div>
                    <div class="rounded-3xl border border-white bg-white/70 p-4 shadow-sm">
                        <p class="font-semibold text-gray-950">3. Retirada</p>
                        <p class="mt-1">Você recebe retorno automático.</p>
                    </div>
                </div>
            </section>

            <section class="flex items-center px-6 pb-8 sm:px-10 lg:col-span-7 lg:px-14 lg:py-14">
                <div class="w-full rounded-[2rem] border border-white bg-white p-6 shadow-[0_24px_80px_rgba(17,24,39,0.08)] sm:p-8">
                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('teacher-requests.store') }}" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        @csrf
                        <div>
                            <label for="teacher_name" class="mb-2 block text-sm font-semibold text-gray-900">Nome</label>
                            <input id="teacher_name" name="teacher_name" value="{{ old('teacher_name') }}" required class="senai-input" placeholder="Nome do professor">
                        </div>
                        <div>
                            <label for="teacher_email" class="mb-2 block text-sm font-semibold text-gray-900">E-mail</label>
                            <input id="teacher_email" name="teacher_email" type="email" value="{{ old('teacher_email') }}" required class="senai-input" placeholder="professor@senai.br">
                        </div>
                        <div>
                            <label for="course_name" class="mb-2 block text-sm font-semibold text-gray-900">Curso</label>
                            <input id="course_name" name="course_name" list="courses" value="{{ old('course_name') }}" required class="senai-input" placeholder="Ex: Desenvolvimento de Sistemas">
                            <datalist id="courses">
                                @foreach ($courses as $course)
                                    <option value="{{ $course->nome_curso }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label for="class_name" class="mb-2 block text-sm font-semibold text-gray-900">Turma</label>
                            <input id="class_name" name="class_name" list="classes" value="{{ old('class_name') }}" required class="senai-input" placeholder="Ex: DS-1A">
                            <datalist id="classes">
                                @foreach ($classes as $class)
                                    <option value="{{ $class->nome_turma }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="md:col-span-2">
                            <label for="book_id" class="mb-2 block text-sm font-semibold text-gray-900">Livro didático</label>
                            <select id="book_id" name="book_id" required class="senai-input">
                                <option value="">Selecione o material</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>
                                        {{ $book->title }} | {{ $book->subject }} | saldo {{ $book->quantity }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quantity" class="mb-2 block text-sm font-semibold text-gray-900">Quantidade</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="500" value="{{ old('quantity') }}" required class="senai-input" placeholder="Ex: 30">
                        </div>
                        <div>
                            <label for="due_date" class="mb-2 block text-sm font-semibold text-gray-900">Data desejada</label>
                            <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" class="senai-input">
                        </div>
                        <div class="md:col-span-2">
                            <label for="notes" class="mb-2 block text-sm font-semibold text-gray-900">Observações</label>
                            <textarea id="notes" name="notes" rows="4" class="senai-input resize-none" placeholder="Detalhe aula, turno, prioridade ou instruções de retirada.">{{ old('notes') }}</textarea>
                        </div>
                        <div class="flex flex-col gap-3 md:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('employee.login') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-950">Acesso do almoxarifado</a>
                            <button type="submit" class="rounded-2xl bg-gray-950 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                                Enviar solicitação
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
