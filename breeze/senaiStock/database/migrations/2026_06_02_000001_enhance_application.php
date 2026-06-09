<?php

use App\Models\Book;
use App\Models\Funcionario;
use App\Models\Livro;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add password column to funcionarios
        if (! Schema::hasColumn('funcionarios', 'password')) {
            Schema::table('funcionarios', function (Blueprint $table) {
                $table->string('password')->nullable()->after('Cpf');
            });
        }

        try {
            Schema::table('movements', function (Blueprint $table) {
                $table->index('type', 'movements_type_index');
                $table->index('created_at', 'movements_created_at_index');
            });
        } catch (Exception $e) {
            // Index may already exist
        }

        try {
            Schema::table('books', function (Blueprint $table) {
                $table->index('title', 'books_title_index');
            });
        } catch (Exception $e) {
            // Index may already exist
        }

        // 2. Add author/publisher/pages columns to books (removing hardcoded metadata)
        if (! Schema::hasColumn('books', 'author')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('author')->nullable()->after('title');
                $table->string('publisher')->nullable()->after('author');
                $table->unsignedInteger('pages')->nullable()->after('publisher');
                $table->year('publication_year')->nullable()->after('pages');
            });
        }

        // 3. Migrate data from config books to DB books (author/publisher/pages)
        $configBooks = config('senaistock.books', []);
        foreach ($configBooks as $configBook) {
            $book = Book::find($configBook['id']);
            if ($book && empty($book->author)) {
                $book->update([
                    'author' => $configBook['author'] ?? null,
                    'publisher' => $configBook['publisher'] ?? null,
                    'pages' => $configBook['pages'] ?? null,
                    'publication_year' => $configBook['year'] ?? null,
                ]);
            }
        }

        // 4. Copy data from livros to books if livros has data and books doesn't
        if (Schema::hasTable('livros') && Livro::query()->exists() && ! Book::query()->exists()) {
            Livro::query()->each(function (Livro $livro) {
                Book::create([
                    'title' => $livro->Titulo ?: 'Livro sem titulo',
                    'isbn' => $livro->Isbn ?: 'SEM-'.strtoupper(substr(md5(uniqid()), 0, 8)),
                    'subject' => $livro->Categoria ?: 'Geral',
                    'quantity' => 0,
                    'description' => 'Importado da tabela livros.',
                    'status' => 'ativo',
                ]);
            });
        }

        // 5. Set default passwords for existing funcionarios
        if (Funcionario::query()->exists()) {
            Funcionario::whereNull('password')->each(function (Funcionario $f) {
                $f->update(['password' => 'senai123']);
            });
        }

        // 6. Simplify suppliers - only Editora Senai
        if (Schema::hasTable('suppliers')) {
            DB::table('suppliers')->where('name', '!=', 'Editora SENAI-SP')->delete();
            DB::table('suppliers')->where('name', 'Editora SENAI-SP')->update([
                'contact_name' => 'Setor de Compras',
                'email' => 'compras@editorasenai.com.br',
                'phone' => '(11) 3000-0101',
                'lead_time_days' => 7,
                'status' => 'ativo',
            ]);
        }

        // 7. Create stock_notifications for low stock books
        $threshold = (int) config('senaistock.low_stock_threshold', 8);
        Book::where('quantity', '<', $threshold)->each(function (Book $book) {
            $existingNotification = DB::table('stock_notifications')
                ->where('book_id', $book->id)
                ->where('type', 'stock')
                ->whereNull('read_at')
                ->exists();

            if (! $existingNotification) {
                DB::table('stock_notifications')->insert([
                    'type' => 'stock',
                    'severity' => 'critical',
                    'title' => 'Estoque critico: '.$book->title,
                    'body' => 'Saldo atual: '.$book->quantity.' unidade(s).',
                    'action_url' => '/dashboard/library',
                    'book_id' => $book->id,
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn('password');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropColumn(['author', 'publisher', 'pages', 'publication_year']);
        });
    }
};
