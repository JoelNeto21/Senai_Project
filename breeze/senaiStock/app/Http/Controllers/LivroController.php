<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use Illuminate\Http\Request;

class LivroController extends Controller
{
    // Display a list of all books
    public function index()
    {
        $livros = Livro::all();
        return view('livros.index', compact('livros'));
    }

    // Show form to create a new book
    public function create()
    {
        return view('livros.create');
    }

    // Save a new book
    public function store(Request $request)
    {
        $request->validate([
            'Isbn' => 'required|unique:livros,Isbn',
            'Titulo' => 'required|string|max:255',
        ]);

        Livro::create($request->all());

        return redirect()->route('livros.index')->with('success', 'Livro cadastrado com sucesso!');
    }

    // Show form to edit a book
    public function edit(Livro $livro)
    {
        return view('livros.edit', compact('livro'));
    }

    // Update book details
    public function update(Request $request, Livro $livro)
    {
        $request->validate([
            'Isbn' => 'required|unique:livros,Isbn,' . $livro->id,
            'Titulo' => 'required|string|max:255',
        ]);

        $livro->update($request->all());

        return redirect()->route('livros.index')->with('success', 'Livro atualizado!');
    }

    // Remove a book
    public function destroy(Livro $livro)
    {
        $livro->delete();
        return redirect()->route('livros.index')->with('success', 'Livro removido.');
    }
}