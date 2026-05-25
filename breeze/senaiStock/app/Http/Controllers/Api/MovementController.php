<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Movement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $movements = Movement::with(['book', 'user'])
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->latest('created_at')
            ->get();

        return response()->json($movements);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Movement::with(['book', 'user'])->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:entrada,saida'],
            'book_id' => ['required', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'justification' => ['required_if:type,saida', 'nullable', 'string'],
            'funcionario_id' => ['nullable', 'integer', 'exists:funcionarios,Id_funcionario'],
        ]);

        $movement = DB::transaction(function () use ($data, $request) {
            $book = Book::query()->lockForUpdate()->findOrFail($data['book_id']);

            if ($data['type'] === 'saida' && (int) $data['quantity'] > $book->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock for saida',
                ]);
            }

            if ($data['type'] === 'saida') {
                $book->decrement('quantity', (int) $data['quantity']);
            } else {
                $book->increment('quantity', (int) $data['quantity']);
            }

            return Movement::create([
                'type' => $data['type'],
                'book_id' => $book->id,
                'user_id' => $request->user()?->id,
                'funcionario_id' => $data['funcionario_id'] ?? null,
                'quantity' => (int) $data['quantity'],
                'justification' => $data['justification'] ?? null,
            ]);
        });

        return response()->json($movement->fresh(), 201);
    }
}
