<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFuncionarioAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('employee.id')) {
            return redirect()->route('employee.login');
        }

        return $next($request);
    }
}