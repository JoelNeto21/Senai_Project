<?php

/**
 * QUERIES OTIMIZADAS - SenaiStock
 * 
 * Exemplos de queries otimizadas com Eloquent ORM
 * para evitar N+1 queries e melhorar performance
 * 
 * Padrão: Sempre usar eager loading com with()
 */

namespace App\Queries;

use App\Models\Book;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OptimizedQueries
{
    /**
     * ✅ CORRETO: Obter todos os movimentos com eager loading
     * 
     * Uma única query para movements + uma para books + uma para users
     * Total: 3 queries
     */
    public static function getAllMovementsOptimized()
    {
        return Movement::with(['book', 'user', 'funcionario'])
            ->get();
    }

    /**
     * ❌ ERRADO: Causa N+1 queries
     * 
     * Uma query para movements + uma query por movimento (N+1)
     * Total: 1 + N queries (muito lento com muitos dados)
     */
    public static function getAllMovementsNotOptimized()
    {
        $movements = Movement::all();
        foreach ($movements as $movement) {
            echo $movement->book->title; // Query executada aqui!
        }
    }

    /**
     * ✅ CORRETO: Obter livros com poucos movimentos
     * 
     * Usando count() agregado e filtered relationships
     */
    public static function getBooksWithMovementCount()
    {
        return Book::withCount('movements')
            ->get();
    }

    /**
     * ✅ CORRETO: Obter livros com estoque crítico
     */
    public static function getCriticalStockBooks()
    {
        return Book::where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->get();
    }

    /**
     * ✅ CORRETO: Obter movimentos de entrada do último mês
     */
    public static function getLastMonthEntries()
    {
        return Movement::with(['book', 'user'])
            ->where('type', 'entrada')
            ->where('created_at', '>=', now()->subMonth())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * ✅ CORRETO: Obter livros com movimentos filtrados
     * 
     * Eager loading com constraint
     */
    public static function getBooksWithRecentMovements()
    {
        return Book::with([
            'movements' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(7))
                    ->orderBy('created_at', 'desc');
            }
        ])->get();
    }

    /**
     * ✅ CORRETO: Obter movimentos por usuário com agregação
     */
    public static function getMovementsByUserWithStats()
    {
        return User::with(['movements' => function ($query) {
            $query->select('id', 'user_id', 'type', 'quantity', 'created_at');
        }])
            ->withCount('movements')
            ->get();
    }

    /**
     * ✅ CORRETO: Obter estatísticas de movimento por tipo
     */
    public static function getMovementStatistics()
    {
        return Movement::with('book')
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('type')
            ->get();
    }

    /**
     * ✅ CORRETO: Obter livros com ranking de movimentos
     */
    public static function getTopMovedBooks($limit = 10)
    {
        return Book::withCount('movements')
            ->orderBy('movements_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ✅ CORRETO: Obter movimentos com todas as relações
     * Melhor para transformação em JSON API
     */
    public static function getMovementsForApi()
    {
        return Movement::with([
            'book:id,title,isbn,subject,quantity',
            'user:id,name,email',
            'funcionario:Id_funcionario,Nome,Cpf'
        ])
            ->select('id', 'type', 'book_id', 'user_id', 'funcionario_id', 'quantity', 'justification', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * ✅ CORRETO: Buscar movimento com filtros múltiplos
     */
    public static function searchMovements($filters = [])
    {
        $query = Movement::with(['book', 'user']);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['book_id'])) {
            $query->where('book_id', $filters['book_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * ✅ CORRETO: Obter relatório de estoque
     */
    public static function getStockReport()
    {
        return Book::select([
            'id',
            'title',
            'isbn',
            'subject',
            'quantity',
            DB::raw('CASE WHEN quantity <= 0 THEN "Sem Estoque" WHEN quantity < 10 THEN "Crítico" ELSE "Normal" END as status')
        ])
            ->orderBy('quantity', 'asc')
            ->get();
    }

    /**
     * ✅ CORRETO: Obter livro com suas movimentações resumidas
     */
    public static function getBookWithMovementSummary($bookId)
    {
        return Book::with([
            'movements' => function ($query) {
                $query->select('id', 'book_id', 'type', 'quantity', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(10);
            }
        ])
            ->withCount('movements')
            ->findOrFail($bookId);
    }

    /**
     * ✅ CORRETO: Paginar movimentos com eager loading
     */
    public static function paginateMovements($perPage = 15)
    {
        return Movement::with(['book', 'user', 'funcionario'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * ✅ CORRETO: Obter movimentos de um período específico
     */
    public static function getMovementsByDateRange($from, $to)
    {
        return Movement::with(['book', 'user'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * ✅ CORRETO: Obter resumo de movimentos por dia
     */
    public static function getDailyMovementSummary()
    {
        return Movement::select(
            DB::raw('DATE(created_at) as date'),
            'type',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(quantity) as total_quantity')
        )
            ->groupBy('date', 'type')
            ->orderBy('date', 'desc')
            ->get();
    }
}
