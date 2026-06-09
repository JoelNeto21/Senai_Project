<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Support\EmployeeRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'password' => ['required', 'string', 'min:4'],
        ]);

        $funcionario = Funcionario::with('cargo')
            ->where('NIF', $credentials['nif'])
            ->first();

        if (! $funcionario || ! Hash::check($credentials['password'], $funcionario->password ?? '')) {
            return back()->withErrors([
                'nif' => 'Credenciais inválidas para o funcionário informado.',
            ])->withInput($request->only('nif'));
        }

        if (! in_array($funcionario->cargo?->Nome_cargo, [EmployeeRole::COORDENADOR, EmployeeRole::PROFESSOR], true)) {
            return back()->withErrors([
                'nif' => 'Este cargo não possui acesso ao sistema.',
            ])->withInput($request->only('nif'));
        }

        $roleKey = $this->roleKey($funcionario->cargo?->Nome_cargo);

        $request->session()->regenerate();
        $request->session()->put('employee', [
            'id' => $funcionario->Id_funcionario,
            'name' => $funcionario->Nome,
            'cargo' => $funcionario->cargo?->Nome_cargo,
            'role_key' => $roleKey,
            'nif' => $funcionario->NIF,
        ]);

        return redirect()->route('senai.dashboard', [
            'view' => EmployeeRole::defaultView($funcionario->cargo?->Nome_cargo),
        ]);
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
