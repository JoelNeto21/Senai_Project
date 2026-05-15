<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequisicaoController extends Controller
{
    public function index(){
        $requisicao = Requisicao::all();
        return view('requisicao.index', compact('requisicao'));
    }

    public function store(Request $request)
{
    $request->validate([
        'data_requisicao' => 'required|date',
        'livro_id' => 'required|exists:livros,id',
        'turma_id' => 'required|exists:turmas,id',
        'funcionario_id' => 'required|exists:funcionarios,id',
    ]);

    Requisicao::create([
        'data_requisicao' => $request->data_requisicao,
        'livro_id' => $request->livro_id,
        'turma_id' => $request->turma_id,
        'funcionario_id' => $request->funcionario_id,
    ]);

    return redirect()->back()->with('success', 'Requisição criada!');
}
}
