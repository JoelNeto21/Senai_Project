<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\TeacherRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeacherRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (TeacherRequest::query()->exists() || !Book::query()->exists()) {
            return;
        }

        $books = Book::query()->orderBy('subject')->orderBy('title')->limit(4)->get();
        $teachers = [
            ['teacher_name' => 'Prof. Carlos Mendes', 'teacher_email' => 'carlos.mendes@escola.senai.br', 'class_name' => 'MEC-2A'],
            ['teacher_name' => 'Profa. Ana Paula', 'teacher_email' => 'ana.paula@escola.senai.br', 'class_name' => 'DS-1B'],
            ['teacher_name' => 'Prof. Roberto Alves', 'teacher_email' => 'roberto.alves@escola.senai.br', 'class_name' => 'ELE-3C'],
            ['teacher_name' => 'Profa. Fernanda Lima', 'teacher_email' => 'fernanda.lima@escola.senai.br', 'class_name' => 'ADM-1A'],
        ];

        $books->values()->each(function (Book $book, int $index) use ($teachers): void {
            $requestedQuantity = match ($index) {
                0 => max(1, min($book->quantity, 18)),
                1 => $book->quantity + 5,
                2 => max(1, min($book->quantity, 45)),
                default => $book->quantity + 10,
            };

            TeacherRequest::create($teachers[$index] + [
                'protocol' => 'SS-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'subject' => $book->subject,
                'book_id' => $book->id,
                'title' => $book->title,
                'quantity' => $requestedQuantity,
                'status' => $index === 2 ? 'atendido' : 'pendente',
                'due_date' => now()->addDays(3 + $index)->toDateString(),
                'notes' => 'Pedido inicial para demonstracao do fluxo do almoxarifado.',
            ]);
        });
    }
}
