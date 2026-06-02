<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'Matemática Fundamental - Conceitos e Aplicações',
                'isbn' => '978-8534965124',
                'subject' => 'Matemática',
                'quantity' => 35,
            ],
            [
                'title' => 'Português: Análise de Textos e Produção Escrita',
                'isbn' => '978-8526856325',
                'subject' => 'Português',
                'quantity' => 42,
            ],
            [
                'title' => 'História do Brasil: Desde a Colonização até os Dias Atuais',
                'isbn' => '978-8535724356',
                'subject' => 'História',
                'quantity' => 28,
            ],
            [
                'title' => 'Ciências Naturais: Biologia, Física e Química',
                'isbn' => '978-8535725673',
                'subject' => 'Ciências',
                'quantity' => 8,  // Critical stock
            ],
            [
                'title' => 'English for Modern Learners - Intermediate Level',
                'isbn' => '978-0134389745',
                'subject' => 'Inglês',
                'quantity' => 25,
            ],
            [
                'title' => 'Geografia Física e Humana - Análise Geoespacial',
                'isbn' => '978-8535728491',
                'subject' => 'Geografia',
                'quantity' => 15,
            ],
            [
                'title' => 'Educação Física: Esporte, Saúde e Movimento',
                'isbn' => '978-8525066027',
                'subject' => 'Educação Física',
                'quantity' => 5,  // Critical stock
            ],
            [
                'title' => 'Artes Visuais e Expressão Criativa',
                'isbn' => '978-8535727578',
                'subject' => 'Artes',
                'quantity' => 12,
            ],
        ];

        foreach ($books as $book) {
            Book::firstOrCreate(
                ['isbn' => $book['isbn']],
                $book + [
                    'description' => 'Livro didatico utilizado em turmas SENAI-SP.',
                    'minimum_stock' => 10,
                    'location' => 'Almoxarifado central',
                    'status' => 'ativo',
                ]
            );
        }
    }
}
