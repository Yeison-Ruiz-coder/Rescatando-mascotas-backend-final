<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Para peticiones API, no redirigir
        if ($request->is('api/*')) {
            return null;
        }
        
        // Para peticiones que esperan JSON, no redirigir
        if ($request->expectsJson()) {
            return null;
        }
        
        // Intentar obtener la ruta 'login', si no existe retornar null
        try {
            return route('login');
        } catch (\Exception $e) {
            return null;
        }
    }
}