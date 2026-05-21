<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movement;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class MovementController extends Controller
{
    public function index(): JsonResponse
    {
        $movements = Movement::with(['book', 'user'])
            ->latest('created_at')
            ->get();

        return response()->json(['message' => 'Movements retrieved', 'data' => $movements]);
    }

    public function show($id): JsonResponse
    {
        $movement = Movement::with(['book', 'user'])->findOrFail($id);
        return response()->json(['message' => 'Movement found', 'data' => $movement]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:entrada,saida',
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'justification' => 'nullable|string',
            'funcionario_id' => 'nullable|integer',
        ]);

        $book = Book::findOrFail($data['book_id']);

        if ($data['type'] === 'saida') {
            if (empty($data['justification'])) {
                return response()->json(['message' => 'Justification is required for saída'], 422);
            }

            if ($data['quantity'] > $book->quantity) {
                return response()->json(['message' => 'Insufficient stock for saída'], 422);
            }

            $book->decrement('quantity', $data['quantity']);
        } else {
            $book->increment('quantity', $data['quantity']);
        }

        $movement = Movement::create([
            'type' => $data['type'],
            'book_id' => $data['book_id'],
            'quantity' => $data['quantity'],
            'justification' => $data['justification'] ?? null,
            'funcionario_id' => $data['funcionario_id'] ?? null,
        ]);

        return response()->json(['message' => 'Movement created', 'data' => $movement], 201);
    }
}

