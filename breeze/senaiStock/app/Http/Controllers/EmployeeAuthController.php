<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $roleKey = $this->roleKey($funcionario->cargo?->Nome_cargo);

        if ($roleKey === 'professor') {
            return back()->withErrors([
                'nif' => 'Professores devem usar a area publica de solicitacao, sem login interno.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('employee', [
            'id' => $funcionario->Id_funcionario,
            'name' => $funcionario->Nome,
            'cargo' => $funcionario->cargo?->Nome_cargo,
            'role_key' => $roleKey,
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

    private function roleKey(?string $role): string
    {
        return Str::of($role ?? '')
            ->ascii()
            ->lower()
            ->replace(' ', '_')
            ->toString();
    }
}
