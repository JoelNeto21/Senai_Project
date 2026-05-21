<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Books retrieved', 'data' => Book::all()]);
    }

    public function show($id): JsonResponse
    {
        $book = Book::findOrFail($id);
        return response()->json(['message' => 'Book found', 'data' => $book]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
        ]);

        $book = Book::create($data);

        return response()->json(['message' => 'Book created', 'data' => $book], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'isbn' => 'sometimes|nullable|string|max:100',
            'subject' => 'sometimes|nullable|string|max:255',
            'quantity' => 'sometimes|required|integer|min:0',
        ]);

        $book->update($data);

        return response()->json(['message' => 'Book updated', 'data' => $book]);
    }

    public function destroy($id): JsonResponse
    {
        $book = Book::findOrFail($id);
        $book->delete();
        return response()->json(['message' => 'Book deleted']);
    }
}
