<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MovementSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();
        $users = User::all();

        if ($books->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Nenhum livro ou usuário encontrado. Execute BookSeeder primeiro.');
            return;
        }

        $movements = [];

        // Movimentos de entrada (reposição de estoque)
        $movements[] = [
            'type' => 'entrada',
            'book_id' => $books->first()->id,
            'user_id' => $users->first()->id,
            'quantity' => 20,
            'justification' => 'Reposição de estoque - pedido 2025/001',
        ];

        $movements[] = [
            'type' => 'entrada',
            'book_id' => $books->skip(1)->first()->id,
            'user_id' => $users->first()->id,
            'quantity' => 15,
            'justification' => 'Doação de livros - projeto escolar',
        ];

        // Movimentos de saída (utilização)
        $movements[] = [
            'type' => 'saida',
            'book_id' => $books->skip(2)->first()->id,
            'user_id' => $users->last()->id,
            'quantity' => 8,
            'justification' => 'Distribuição para turma 10A - aula de história',
        ];

        $movements[] = [
            'type' => 'saida',
            'book_id' => $books->skip(3)->first()->id,
            'user_id' => $users->first()->id,
            'quantity' => 5,
            'justification' => 'Empréstimo para laboratório de ciências',
        ];

        $movements[] = [
            'type' => 'entrada',
            'book_id' => $books->skip(4)->first()->id,
            'user_id' => $users->first()->id,
            'quantity' => 10,
            'justification' => 'Reposição - estoque crítico',
        ];

        foreach ($movements as $movement) {
            Movement::create($movement);
        }

        $this->command->info('Movimentos de estoque criados com sucesso!');
    }
}
