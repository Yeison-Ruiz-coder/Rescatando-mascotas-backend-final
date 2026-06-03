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
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $key = 'public:response:' . md5($request->getRequestUri() . '|' . serialize($request->query()));

        return Cache::remember($key, now()->addMinutes($minutes), function () use ($next, $request) {
            return $next($request);
        });
    }
}
