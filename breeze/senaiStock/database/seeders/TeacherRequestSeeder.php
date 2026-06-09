<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Funcionario;
use App\Models\TeacherRequest;
use App\Models\Turma;
use App\Services\TeacherRequestService;
use Illuminate\Database\Seeder;

class TeacherRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (TeacherRequest::query()->exists() || !Book::query()->exists()) {
            return;
        }

        $books = Book::query()->orderBy('subject')->orderBy('title')->limit(4)->get();
        $turmas = Turma::with('curso')->orderBy('id')->get();
        $professor = Funcionario::where('NIF', 654321)->firstOrFail();
        $service = app(TeacherRequestService::class);

        $books->values()->each(function (Book $book, int $index) use ($turmas, $professor, $service): void {
            $turma = $turmas[$index % $turmas->count()];
            $requestedQuantity = match ($index) {
                0 => max(1, min($book->quantity, 18)),
                1 => $book->quantity + 5,
                2 => max(1, min($book->quantity, 45)),
                default => $book->quantity + 10,
            };

            $service->create([
                'requested_by_funcionario_id' => $professor->Id_funcionario,
                'teacher_name' => $professor->Nome,
                'teacher_email' => 'professor@senai.br',
                'class_name' => $turma->nome_turma,
                'course_name' => $turma->curso?->nome_curso,
                'book_id' => $book->id,
                'quantity' => $requestedQuantity,
                'due_date' => now()->addDays(3 + $index)->toDateString(),
                'notes' => 'Pedido inicial para demonstracao do fluxo da coordenacao.',
            ]);
        });
    }
}
