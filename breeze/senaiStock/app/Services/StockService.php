<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Funcionario;
use App\Models\Movement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function receiveExisting(Book $book, int $quantity, ?int $funcionarioId, ?string $notes = null): Movement
    {
        return DB::transaction(function () use ($book, $quantity, $funcionarioId, $notes): Movement {
            $funcionarioId = $this->validFuncionarioId($funcionarioId);
            $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);
            $lockedBook->increment('quantity', $quantity);

            return Movement::create([
                'type' => 'entrada',
                'book_id' => $lockedBook->id,
                'funcionario_id' => $funcionarioId,
                'quantity' => $quantity,
                'justification' => $notes ?: 'Recebimento de material existente.',
            ]);
        });
    }

    public function createBookWithOpeningStock(array $data, ?int $funcionarioId): Book
    {
        return DB::transaction(function () use ($data, $funcionarioId): Book {
            $funcionarioId = $this->validFuncionarioId($funcionarioId);
            $book = Book::create([
                'title' => $data['title'],
                'isbn' => $data['isbn'] ?: 'SEM-' . Str::upper(Str::random(8)),
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'quantity' => (int) $data['quantity'],
                'minimum_stock' => (int) ($data['minimum_stock'] ?? config('senaistock.low_stock_threshold', 8)),
                'location' => $data['location'] ?? null,
                'status' => $data['status'] ?? 'ativo',
            ]);

            Movement::create([
                'type' => 'entrada',
                'book_id' => $book->id,
                'funcionario_id' => $funcionarioId,
                'quantity' => (int) $data['quantity'],
                'justification' => $data['description'] ?: 'Cadastro inicial de novo material.',
            ]);

            return $book;
        });
    }

    public function withdraw(Book $book, int $quantity, ?int $funcionarioId, string $justification): Movement
    {
        return DB::transaction(function () use ($book, $quantity, $funcionarioId, $justification): Movement {
            $funcionarioId = $this->validFuncionarioId($funcionarioId);
            $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);

            if ($quantity > $lockedBook->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Saldo insuficiente para {$lockedBook->title}. Solicitado: {$quantity}, disponivel: {$lockedBook->quantity}.",
                ]);
            }

            $lockedBook->decrement('quantity', $quantity);

            return Movement::create([
                'type' => 'saida',
                'book_id' => $lockedBook->id,
                'funcionario_id' => $funcionarioId,
                'quantity' => $quantity,
                'justification' => $justification,
            ]);
        });
    }

    public function withdrawBatch(Collection $items, string $destination, ?int $funcionarioId): void
    {
        DB::transaction(function () use ($items, $destination, $funcionarioId): void {
            $funcionarioId = $this->validFuncionarioId($funcionarioId);
            $totalsByBook = $items
                ->groupBy('book_id')
                ->map(fn (Collection $rows) => $rows->sum('quantity'));

            $books = Book::query()
                ->whereIn('id', $totalsByBook->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($totalsByBook as $bookId => $quantity) {
                $book = $books->get($bookId);

                if (!$book || $quantity > $book->quantity) {
                    $title = $book?->title ?? 'material selecionado';
                    $available = $book?->quantity ?? 0;

                    throw ValidationException::withMessages([
                        'items' => "Saldo insuficiente para {$title}. Solicitado: {$quantity}, disponivel: {$available}.",
                    ]);
                }
            }

            foreach ($totalsByBook as $bookId => $quantity) {
                $book = $books->get($bookId);
                $book->decrement('quantity', $quantity);

                Movement::create([
                    'type' => 'saida',
                    'book_id' => $book->id,
                    'funcionario_id' => $funcionarioId,
                    'quantity' => $quantity,
                    'justification' => 'Retirada em lote para ' . $destination . '.',
                ]);
            }
        });
    }

    private function validFuncionarioId(?int $funcionarioId): ?int
    {
        return $funcionarioId && Funcionario::whereKey($funcionarioId)->exists()
            ? $funcionarioId
            : null;
    }
}
