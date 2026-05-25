<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Book::all());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Book::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:100', 'unique:books,isbn'],
            'subject' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['quantity'] = $data['quantity'] ?? 0;

        return response()->json(Book::create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'isbn' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('books', 'isbn')->ignore($book->id)],
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $book->update($data);

        return response()->json($book->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        Book::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
