<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        if ($role === 'admin' && !$request->user()->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
