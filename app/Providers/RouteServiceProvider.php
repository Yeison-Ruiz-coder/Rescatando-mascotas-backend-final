<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    // Solo mantener HOME si usas redirección web
    public const HOME = '/';

    // Estas no se usan en API, pueden ser null
    public const ADMIN_DASHBOARD = null;
    public const FUNDACION_DASHBOARD = null;
    public const VETERINARIA_DASHBOARD = null;

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Si NO usas web, puedes comentar esta línea
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Demasiadas peticiones. Por favor, espera un momento.'
                    ], 429);
                });
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
