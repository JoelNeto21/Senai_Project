<?php

namespace App\Http\Controllers;

use App\Models\Requisicao;
use Illuminate\Http\Request;

class RequisicaoController extends Controller
{
    public function index()
    {
        $requisicao = Requisicao::all();

        return view('requisicao', compact('requisicao'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'data_requisicao' => 'required|date',
            'livro_id' => 'required|exists:livros,id',
            'turma_id' => 'required|exists:turmas,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
        ]);

        Requisicao::create($data);

        return redirect()->back()->with('success', 'Requisição criada!');
    }
}
