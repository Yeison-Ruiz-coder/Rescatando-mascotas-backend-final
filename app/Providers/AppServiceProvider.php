<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use App\Services\Auth\AuthService;
use App\Services\UsuarioService;
use Illuminate\Support\ServiceProvider;
use App\Services\DashboardService;
use App\Services\RazaService;
use App\Services\MascotaService;
use App\Services\TipoVacunaService;
use App\Services\FundacionService;
use App\Services\EventoService;
use App\Services\ComentarioService;
use App\Services\DonacionService;
use App\Services\SolicitudService;
use App\Services\AdopcionService;
use App\Services\SeguimientoService;
use App\Services\RescateService;
use App\Services\ReporteService;
use App\Services\NotificacionService;
use App\Services\VeterinariaService;
use App\Services\Entity\MascotaEntityService;
use App\Services\Entity\RescateEntityService;
use App\Services\Entity\SolicitudEntityService;
use App\Services\Public\MascotaPublicService;
use App\Services\Public\AdopcionPublicService;
use App\Services\Public\EventoPublicService;
use App\Services\Public\FundacionPublicService;
use App\Services\Public\VeterinariaPublicService;
use App\Services\Public\ComentarioPublicService;
use App\Services\Public\RescatePublicService;
use App\Services\User\ComentarioUserService;
use App\Services\User\DonacionUserService;
use App\Services\User\ProfileUserService;
use App\Services\User\SolicitudUserService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->singleton(AuthService::class, fn($app) => new AuthService());

        $this->app->singleton(UsuarioService::class, function ($app) {
            return new UsuarioService();
        });

        $this->app->singleton(NotificacionService::class, function ($app) {
            return new NotificacionService();
        });

        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService();
        });

        $this->app->singleton(ReporteService::class, function ($app) {
            return new ReporteService();
        });

        $this->app->singleton(SeguimientoService::class, function ($app) {
            return new SeguimientoService();
        });

        $this->app->singleton(RescateService::class, function ($app) {
            return new RescateService();
        });

        $this->app->singleton(AdopcionService::class, function ($app) {
            return new AdopcionService();
        });

        $this->app->singleton(SolicitudService::class, function ($app) {
            return new SolicitudService();
        });

        $this->app->singleton(VeterinariaService::class, function ($app) {
            return new VeterinariaService();
        });

        $this->app->singleton(RazaService::class, function ($app) {
            return new RazaService();
        });

        $this->app->singleton(MascotaService::class, function ($app) {
            return new MascotaService();
        });

        $this->app->singleton(TipoVacunaService::class, function ($app) {
            return new TipoVacunaService();
        });

        $this->app->singleton(EventoService::class, function ($app) {
            return new EventoService();
        });

        $this->app->singleton(FundacionService::class, function ($app) {
            return new FundacionService();
        });

        $this->app->singleton(ComentarioService::class, function ($app) {
            return new ComentarioService();
        });

        $this->app->singleton(DonacionService::class, function ($app) {
            return new DonacionService();
        });

        $this->app->singleton(MascotaEntityService::class, function ($app) {
            return new MascotaEntityService();
        });

        $this->app->singleton(RescateEntityService::class, function ($app) {
            return new RescateEntityService();
        });

        $this->app->singleton(SolicitudEntityService::class, function ($app) {
            return new SolicitudEntityService();
        });

        $this->app->singleton(MascotaPublicService::class, fn($app) => new MascotaPublicService());
        $this->app->singleton(AdopcionPublicService::class, fn($app) => new AdopcionPublicService());
        $this->app->singleton(EventoPublicService::class, fn($app) => new EventoPublicService());
        $this->app->singleton(FundacionPublicService::class, fn($app) => new FundacionPublicService());
        $this->app->singleton(VeterinariaPublicService::class, fn($app) => new VeterinariaPublicService());
        $this->app->singleton(ComentarioPublicService::class, fn($app) => new ComentarioPublicService());
        $this->app->singleton(RescatePublicService::class, fn($app) => new RescatePublicService());


        $this->app->singleton(ComentarioUserService::class, fn($app) => new ComentarioUserService());
        $this->app->singleton(DonacionUserService::class, fn($app) => new DonacionUserService());
        $this->app->singleton(ProfileUserService::class, fn($app) => new ProfileUserService());
        $this->app->singleton(SolicitudUserService::class, fn($app) => new SolicitudUserService());
    }
}
