<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureFuncionarioRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $currentRole = $request->session()->get('employee.role_key')
            ?: $this->roleKey($request->session()->get('employee.cargo'));

        $allowedRoles = collect($roles)
            ->map(fn (string $role) => $this->roleKey($role))
            ->all();

        abort_unless(in_array($currentRole, $allowedRoles, true), 403);

        return $next($request);
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
