<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $books = [
            // === Desenvolvimento de Sistemas ===
            [
                'title' => 'Lógica de Programação e Algoritmos',
                'isbn' => '978-85-001-0001',
                'subject' => 'Desenvolvimento de Sistemas',
                'quantity' => 40,
                'author' => 'João Araújo',
                'publisher' => 'Editora SENAI-SP',
                'pages' => 320,
                'publication_year' => 2023,
            ],
            [
                'title' => 'Banco de Dados: Modelagem e SQL',
                'isbn' => '978-85-001-0002',
                'subject' => 'Desenvolvimento de Sistemas',
                'quantity' => 35,
                'author' => 'Patrícia Silva',
                'publisher' => 'Érica',
                'pages' => 380,
                'publication_year' => 2022,
            ],
            [
                'title' => 'Desenvolvimento Web com HTML, CSS e JavaScript',
                'isbn' => '978-85-001-0003',
                'subject' => 'Desenvolvimento de Sistemas',
                'quantity' => 50,
                'author' => 'Diego Fernandes',
                'publisher' => 'Casa do Código',
                'pages' => 420,
                'publication_year' => 2024,
            ],
            [
                'title' => 'Redes de Computadores e Infraestrutura',
                'isbn' => '978-85-001-0004',
                'subject' => 'Desenvolvimento de Sistemas',
                'quantity' => 28,
                'author' => 'Gabriel Torres',
                'publisher' => 'Novatec',
                'pages' => 500,
                'publication_year' => 2023,
            ],
            [
                'title' => 'Engenharia de Software: Práticas Ágeis',
                'isbn' => '978-85-001-0005',
                'subject' => 'Desenvolvimento de Sistemas',
                'quantity' => 22,
                'author' => 'Fábio Gomes',
                'publisher' => 'Editora SENAI-SP',
                'pages' => 290,
                'publication_year' => 2024,
            ],
            [
                'title' => 'Versionamento e DevOps com Git',
                'isbn' => '978-85-001-0006',
                'subject' => 'Desenvolvimento de Sistemas',
                'quantity' => 7,
                'author' => 'Carlos Nogueira',
                'publisher' => 'Novatec',
                'pages' => 210,
                'publication_year' => 2024,
            ],

            // === Administração ===
            [
                'title' => 'Administração Geral e Recursos Humanos',
                'isbn' => '978-85-002-0001',
                'subject' => 'Administração',
                'quantity' => 45,
                'author' => 'Maria Oliveira',
                'publisher' => 'Editora SENAI-SP',
                'pages' => 360,
                'publication_year' => 2023,
            ],
            [
                'title' => 'Contabilidade Geral e Gestão Financeira',
                'isbn' => '978-85-002-0002',
                'subject' => 'Administração',
                'quantity' => 30,
                'author' => 'Roberto Campos',
                'publisher' => 'Atlas',
                'pages' => 410,
                'publication_year' => 2022,
            ],
            [
                'title' => 'Marketing Empresarial e Vendas',
                'isbn' => '978-85-002-0003',
                'subject' => 'Administração',
                'quantity' => 25,
                'author' => 'Ana Costa',
                'publisher' => 'Saraiva',
                'pages' => 280,
                'publication_year' => 2023,
            ],
            [
                'title' => 'Direito Empresarial e Legislação Trabalhista',
                'isbn' => '978-85-002-0004',
                'subject' => 'Administração',
                'quantity' => 6,
                'author' => 'Dr. Paulo Mendes',
                'publisher' => 'Editora SENAI-SP',
                'pages' => 340,
                'publication_year' => 2022,
            ],
            [
                'title' => 'Economia e Mercado para Gestão',
                'isbn' => '978-85-002-0005',
                'subject' => 'Administração',
                'quantity' => 20,
                'author' => 'Luciana Tavares',
                'publisher' => 'Atlas',
                'pages' => 310,
                'publication_year' => 2024,
            ],
            [
                'title' => 'Gestão de Projetos: PMBOK e Metodologias',
                'isbn' => '978-85-002-0006',
                'subject' => 'Administração',
                'quantity' => 5,
                'author' => 'Sérgio Almeida',
                'publisher' => 'Editora SENAI-SP',
                'pages' => 260,
                'publication_year' => 2024,
            ],

            // === Eletroeletrônica ===
            [
                'title' => 'Circuitos Elétricos e Análise de Redes',
                'isbn' => '978-85-003-0001',
                'subject' => 'Eletroeletrônica',
                'quantity' => 32,
                'author' => 'Roberto Alves',
                'publisher' => 'Editora SENAI-SP',
                'pages' => 400,
                'publication_year' => 2023,
            ],
            [
                'title' => 'Eletrônica Analógica: Componentes e Aplicações',
                'isbn' => '978-85-003-0002',
                'subject' => 'Eletroeletrônica',
                'quantity' => 28,
                'author' => 'Juliana Lima',
                'publisher' => 'Érica',
                'pages' => 350,
                'publication_year' => 2023,
            ],
            [
                'title' => 'Eletrônica Digital e Microcontroladores',
                'isbn' => '978-85-003-0003',
                'subject' => 'Eletroeletrônica',
                'quantity' => 24,
                'author' => 'Marcos Silva',
                'publisher' => 'Érica',
                'pages' => 380,
                'publication_year' => 2024,
            ],
            [
                'title' => 'Instalações Elétricas Prediais e Industriais',
                'isbn' => '978-85-003-0004',
                'subject' => 'Eletroeletrônica',
                'quantity' => 18,
                'author' => 'Carlos Eduardo',
                'publisher' => 'Editora SENAI-SP',
                'pages' => 290,
                'publication_year' => 2022,
            ],
            [
                'title' => 'CLP e Automação Industrial',
                'isbn' => '978-85-003-0005',
                'subject' => 'Eletroeletrônica',
                'quantity' => 15,
                'author' => 'Fernanda Lima',
                'publisher' => 'Saraiva',
                'pages' => 340,
                'publication_year' => 2023,
            ],
            [
                'title' => 'Sistemas Embarcados e IoT Industrial',
                'isbn' => '978-85-003-0006',
                'subject' => 'Eletroeletrônica',
                'quantity' => 10,
                'author' => 'Vitor Hugo',
                'publisher' => 'Novatec',
                'pages' => 310,
                'publication_year' => 2024,
            ],
        ];

        foreach ($books as $book) {
            Book::firstOrCreate(
                ['isbn' => $book['isbn']],
                $book + [
                    'description' => 'Livro didatico utilizado no curso de ' . $book['subject'] . '.',
                    'minimum_stock' => 8,
                    'location' => 'Almoxarifado central',
                    'status' => 'ativo',
                ]
            );
        }
    }
}