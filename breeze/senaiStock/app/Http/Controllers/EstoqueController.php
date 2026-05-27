<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\Livro;
use Illuminate\Http\Request;

class EstoqueController extends Controller
{
    // List all stock entries with their related books
    public function index()
    {
        $estoques = Estoque::with('livro')->get();
        return view('estoque.index', compact('estoques'));
    }

    // Show form to add stock (needs to pass list of books for the dropdown)
    public function create()
    {
        $livros = Livro::all();
        return view('estoque.create', compact('livros'));
    }

    // Save stock entry
    public function store(Request $request)
    {
        $request->validate([
            'Quantidade' => 'required|integer|min:0',
            'Id_livro_FK' => 'required|exists:livros,id',
        ]);

        Estoque::create($request->all());

        return redirect()->route('estoque.index')->with('success', 'Estoque atualizado!');
    }

    // Show form to edit stock
    public function edit(Estoque $estoque)
    {
        $livros = Livro::all();
        return view('estoque.edit', compact('estoque', 'livros'));
    }

    // Update stock quantity or book link
    public function update(Request $request, Estoque $estoque)
    {
        $request->validate([
            'Quantidade' => 'required|integer|min:0',
            'Id_livro_FK' => 'required|exists:livros,id',
        ]);

        $estoque->update($request->all());

        return redirect()->route('estoque.index')->with('success', 'Estoque modificado com sucesso!');
    }

    // Remove a stock entry
    public function destroy(Estoque $estoque)
    {
        $estoque->delete();
        return redirect()->route('estoque.index')->with('success', 'Entrada de estoque removida.');
    }
}