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
        // Temporarily use temp file until directories are properly created
        $viewPath = 'temp_funcionarios_index';
        if (view()->exists('funcionarios.index')) {
            $viewPath = 'funcionarios.index';
        }
        return view($viewPath, compact('funcionarios'));
    }

    // 2. Mostrar o formulário para criar um novo funcionário
    public function create()
    {
        $cargos = Cargo::all(); // Precisamos disso para o <select> no formulário
        // Temporarily use temp file until directories are properly created
        $viewPath = 'temp_funcionarios_create';
        if (view()->exists('funcionarios.create')) {
            $viewPath = 'funcionarios.create';
        }
        return view($viewPath, compact('cargos'));
    }

    // 3. Salvar o funcionário no banco de dados
    public function store(Request $request)
    {
        // Validação: Garante que os dados estão corretos antes de salvar
        $request->validate([
            'NIF' => 'required|integer|unique:funcionarios,NIF',
            'Nome' => 'required|string|max:255',
            'Cpf' => 'required|string|unique:funcionarios,Cpf',
            'Id_cargo_FK' => 'required|exists:cargos,Id_cargo', // Verifica se o cargo existe
        ]);

        // Cria o registro usando o $guarded = [] que você definiu no Model
        Funcionario::create($request->only(['NIF', 'Nome', 'Cpf', 'Id_cargo_FK']));

        return redirect()->route('funcionarios.index')
                         ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    // 4. Mostrar o formulário de edição
    public function edit(Funcionario $funcionario)
    {
        $cargos = Cargo::all();
        // Temporarily use temp file until directories are properly created
        $viewPath = 'temp_funcionarios_edit';
        if (view()->exists('funcionarios.edit')) {
            $viewPath = 'funcionarios.edit';
        }
        return view($viewPath, compact('funcionario', 'cargos'));
    }

    // 5. Atualizar os dados
    public function update(Request $request, Funcionario $funcionario)
    {
        $request->validate([
            'NIF' => 'required|integer|unique:funcionarios,NIF,' . $funcionario->Id_funcionario . ',Id_funcionario',
            'Nome' => 'required|string|max:255',
            'Cpf' => 'required|string|unique:funcionarios,Cpf,' . $funcionario->Id_funcionario . ',Id_funcionario',
            'Id_cargo_FK' => 'required|exists:cargos,Id_cargo',
        ]);

        $funcionario->update($request->only(['NIF', 'Nome', 'Cpf', 'Id_cargo_FK']));

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
