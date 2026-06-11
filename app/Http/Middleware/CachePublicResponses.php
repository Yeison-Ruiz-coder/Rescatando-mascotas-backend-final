<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CachePublicResponses
{
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        // ✅ EXCLUIR rutas que no deben ser cacheadas
        $excludedRoutes = [
            'api/mascotas/especies',
            'api/mascotas/por-especie',
            'api/mascotas/destacadas',
        ];

        foreach ($excludedRoutes as $route) {
            if ($request->path() === $route || str_contains($request->path(), $route)) {
                return $next($request);
            }
        }

        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $key = 'public:response:' . md5($request->getRequestUri() . '|' . serialize($request->query()));

        try {
            return Cache::remember($key, now()->addMinutes($minutes), function () use ($next, $request) {
                return $next($request);
            });
        } catch (\Exception $e) {
            // Si falla el cache, simplemente ejecuta la petición normal
            return $next($request);
        }
    }
}
