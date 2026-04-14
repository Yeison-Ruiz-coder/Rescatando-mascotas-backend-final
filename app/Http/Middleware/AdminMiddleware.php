<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        // ✅ SOLO admin, NO fundacion ni veterinaria
        if (auth()->user()->tipo !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado - Se requieren permisos de administrador'
            ], 403);
        }

        return $next($request);
    }
}
