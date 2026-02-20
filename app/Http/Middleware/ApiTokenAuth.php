<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     * Validates the 94-character API token from Authorization header
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        $expectedToken = config('app.api_token');
        
        if (!$token || $token !== $expectedToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API token'
            ], 401);
        }
        
        return $next($request);
    }
}
