<?php

namespace App\Http\Middleware;

use App\Support\EmployeeRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        EmployeeRole::authorizeRole($request, ...$roles);

        return $next($request);
    }
}
