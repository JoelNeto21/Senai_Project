<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Cargo; // Importante para carregar os cargos no formulário
use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    // 1. Listar todos os funcionários
    public function index()
    {
        // Usamos with('cargo') para carregar o nome do cargo sem sobrecarregar o banco
        $funcionarios = Funcionario::with('cargo')->get();
        return view('funcionarios.index', compact('funcionarios'));
    }

    // 2. Mostrar o formulário para criar um novo funcionário
    public function create()
    {
        $cargos = Cargo::all(); // Precisamos disso para o <select> no formulário
        return view('funcionarios.create', compact('cargos'));
    }

    // 3. Salvar o funcionário no banco de dados
    public function store(Request $request)
    {
        // Validação: Garante que os dados estão corretos antes de salvar
        $request->validate([
            'Nome' => 'required|string|max:255',
            'Cpf' => 'required|string|unique:funcionarios,Cpf',
            'Id_cargo_FK' => 'required|exists:cargos,id', // Verifica se o cargo existe
        ]);

        // Cria o registro usando o $guarded = [] que você definiu no Model
        Funcionario::create($request->all());

        return redirect()->route('funcionarios.index')
                         ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    // 4. Mostrar o formulário de edição
    public function edit(Funcionario $funcionario)
    {
        $cargos = Cargo::all();
        return view('funcionarios.edit', compact('funcionario', 'cargos'));
    }

    // 5. Atualizar os dados
    public function update(Request $request, Funcionario $funcionario)
    {
        $request->validate([
            'Nome' => 'required|string|max:255',
            'Cpf' => 'required|string|unique:funcionarios,Cpf,' . $funcionario->id,
            'Id_cargo_FK' => 'required|exists:cargos,id',
        ]);

        $funcionario->update($request->all());

        return redirect()->route('funcionarios.index')
                         ->with('success', 'Dados atualizados!');
    }

    // 6. Excluir o funcionário
    public function destroy(Funcionario $funcionario)
    {
        $funcionario->delete();
        return redirect()->route('funcionarios.index')
                         ->with('success', 'Funcionário removido.');
    }
}