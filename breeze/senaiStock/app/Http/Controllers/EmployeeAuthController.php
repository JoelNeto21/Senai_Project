<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.employee-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nif' => ['required', 'integer'],
            'cpf' => ['required', 'string', 'max:14'],
        ]);

        $funcionario = Funcionario::with('cargo')
            ->where('NIF', $credentials['nif'])
            ->where('Cpf', $credentials['cpf'])
            ->first();

        if (!$funcionario) {
            return back()->withErrors([
                'nif' => 'Credenciais inválidas para o funcionário informado.',
            ])->withInput($request->only('nif', 'cpf'));
        }

        $request->session()->regenerate();
        $request->session()->put('employee', [
            'id' => $funcionario->Id_funcionario,
            'name' => $funcionario->Nome,
            'cargo' => $funcionario->cargo?->Nome_cargo,
            'nif' => $funcionario->NIF,
        ]);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('employee');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login');
    }
}
