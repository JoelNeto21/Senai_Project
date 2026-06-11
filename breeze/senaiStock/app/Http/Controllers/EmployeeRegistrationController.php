<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Funcionario;
use App\Support\EmployeeRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class EmployeeRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.employee-register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Nome' => ['required', 'string', 'max:255'],
            'NIF' => ['required', 'integer', 'unique:funcionarios,NIF'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $professor = Cargo::firstOrCreate(['Nome_cargo' => EmployeeRole::PROFESSOR]);

        Funcionario::create([
            'Nome' => $data['Nome'],
            'NIF' => $data['NIF'],
            'Cpf' => null,
            'password' => $data['password'],
            'Id_cargo_FK' => $professor->Id_cargo,
        ]);

        return redirect()->route('employee.login')
            ->with('status', 'Cadastro realizado. Entre com seu NIF e senha.');
    }
}
