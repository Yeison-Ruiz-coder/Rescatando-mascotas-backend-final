<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Entity\EventoController as EntityEventoController;
use App\Http\Controllers\Api\V1\Entity\SuscripcionController as EntitySuscripcionController;
use App\Http\Controllers\Api\V1\Admin\EventoController as AdminEventoController;
use App\Http\Controllers\Api\V1\Admin\SuscripcionController as AdminSuscripcionController;
use App\Http\Controllers\Api\V1\Public\EventoController as PublicEventoController;
use App\Http\Controllers\Api\V1\Public\SuscripcionPublicController as PublicSuscripcionController;

// =========================================================================
// API V1 - RUTAS PÚBLICAS (SIN AUTENTICACIÓN)
// =========================================================================

Route::middleware(['cache.public:10'])->group(function () {

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [App\Http\Controllers\Api\V1\Auth\LoginController::class, 'login']);
        Route::post('/register', [App\Http\Controllers\Api\V1\Auth\RegisterController::class, 'register']);
        Route::get('/check-email', [App\Http\Controllers\Api\V1\Auth\RegisterController::class, 'checkEmail']);
        Route::post('/logout', [App\Http\Controllers\Api\V1\Auth\LogoutController::class, 'logout'])
            ->middleware('auth:sanctum');
        Route::post('/password/email', [App\Http\Controllers\Api\V1\Auth\PasswordResetController::class, 'sendResetLink']);
        Route::post('/password/reset', [App\Http\Controllers\Api\V1\Auth\PasswordResetController::class, 'resetPassword']);
    });

    // Mascotas - Público
    Route::get('/mascotas/especies', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'getEspecies']);
    Route::get('/mascotas/sugerencias', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'sugerencias']);
    Route::get('/mascotas', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'index']);
    Route::get('/mascotas/{id}', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'show']);
    Route::get('/mascotas/especie/{especie}', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'porEspecie']);
    Route::get('/mascotas/fundacion/{fundacionId}', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'porFundacion']);

    // Adopciones - Público
    Route::prefix('adopciones')->name('adopciones.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'index']);
        Route::get('/disponibles', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'disponibles']);
        Route::get('/destacadas', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'destacadas']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'show']);
        Route::get('/{id}/disponibilidad', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'verificarDisponibilidad']);
    });

    // Rescates - Público
    Route::prefix('rescates')->name('rescates.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\Public\RescateController::class, 'index']);
        Route::post('/reportar', [App\Http\Controllers\Api\V1\Public\RescateController::class, 'reportar']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Public\RescateController::class, 'show']);
    });

    // Fundaciones - Público
    Route::prefix('fundaciones')->name('fundaciones.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'index']);
        Route::get('/estadisticas', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'estadisticas']);
        Route::get('/verificadas', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'verificadas']);
        Route::get('/reciben-voluntarios', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'recibenVoluntarios']);
        Route::get('/ciudad/{ciudad}', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'porCiudad']);
        Route::get('/search', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'search']);
        Route::get('/cercanas', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'cercanas']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Public\FundacionController::class, 'show']);
    });

    // Veterinarias - Público
    Route::get('/veterinarias', [App\Http\Controllers\Api\V1\Public\VeterinariaController::class, 'index']);
    Route::get('/veterinarias/{id}', [App\Http\Controllers\Api\V1\Public\VeterinariaController::class, 'show']);
    Route::get('/veterinarias/urgencias/mapa', [App\Http\Controllers\Api\V1\Public\VeterinariaController::class, 'mapa']);
    Route::get('/veterinarias/cercanas', [App\Http\Controllers\Api\V1\Public\VeterinariaController::class, 'cercanas']);

    // =========================================================================
    // RUTAS PÚBLICAS - EVENTOS
    // =========================================================================
    Route::prefix('eventos')->name('eventos.')->group(function () {
        Route::get('/', [PublicEventoController::class, 'index']);
        Route::get('/proximos', [PublicEventoController::class, 'proximos']);
        Route::get('/tipo/{tipo}', [PublicEventoController::class, 'porTipo']);
        Route::get('/{id}', [PublicEventoController::class, 'show']);
        Route::get('/calendario/data', [PublicEventoController::class, 'calendario']);
        Route::post('/{id}/like', [PublicEventoController::class, 'like']);

        // Rutas con autenticación
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/mis-eventos', [PublicEventoController::class, 'misEventos']);
            Route::post('/{id}/confirmar-asistencia', [PublicEventoController::class, 'confirmarAsistencia']);
            Route::delete('/{id}/cancelar-asistencia', [PublicEventoController::class, 'cancelarAsistencia']);
        });
    });

    // =========================================================================
    // RUTAS PÚBLICAS - SUSCRIPCIONES (SOLO PARA VER PLANES)
    // =========================================================================
    Route::prefix('suscripciones')->name('suscripciones.')->group(function () {
        // Público - Ver planes disponibles (sin autenticación)
        Route::get('/planes', [PublicSuscripcionController::class, 'planes']);
        Route::get('/planes/{id}', [PublicSuscripcionController::class, 'planDetalle']);

        // Rutas con autenticación (para usuarios)
        Route::middleware(['auth:sanctum'])->prefix('user')->name('user.')->group(function () {
            Route::get('/mis-suscripciones', [PublicSuscripcionController::class, 'misSuscripciones']);
            Route::post('/crear', [PublicSuscripcionController::class, 'store']);
            Route::get('/{id}', [PublicSuscripcionController::class, 'show']);
            Route::patch('/{id}/cancelar', [PublicSuscripcionController::class, 'cancelar']);
            Route::patch('/{id}/pausar', [PublicSuscripcionController::class, 'pausar']);
            Route::patch('/{id}/reactivar', [PublicSuscripcionController::class, 'reactivar']);
        });
    });
});

// =========================================================================
// API V1 - RUTAS DE USUARIO (REQUIEREN AUTENTICACIÓN)
// =========================================================================

Route::middleware(['auth:sanctum'])->prefix('user')->name('user.')->group(function () {
    // Perfil
    Route::get('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'update']);
    Route::post('/profile/change-password', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'changePassword']);
    Route::delete('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'destroy']);
    Route::post('/profile/avatar', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'deleteAvatar']);
    Route::put('/profile/location', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'updateLocation']);
    Route::put('/profile/social', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'updateSocialNetworks']);
    Route::get('/profile/completion-status', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'getCompletionStatus']);
    Route::post('/profile/verify-phone', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'sendPhoneVerification']);
    Route::post('/profile/verify-phone/confirm', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'confirmPhone']);
    Route::get('/stats', [App\Http\Controllers\Api\V1\User\UserStatsController::class, 'getStats']);
    Route::get('/adopciones', [App\Http\Controllers\Api\V1\User\UserStatsController::class, 'getAdoptions']);
    Route::get('/donaciones', [App\Http\Controllers\Api\V1\User\UserStatsController::class, 'getDonations']);

    // Solicitudes de adopción
    Route::get('/solicitudes', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'index']);
    Route::post('/solicitudes/adopcion', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'storeAdopcion']);
    Route::get('/solicitudes/{id}', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'show']);
    Route::get('/solicitudes-recibidas', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'recibidas']);
    Route::get('/solicitudes-recibidas/pendientes', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'pendientesRecibidas']);
    Route::get('/solicitudes-recibidas/{id}', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'showRecibida']);

    // Donaciones
    Route::get('/donaciones', [App\Http\Controllers\Api\V1\User\DonacionController::class, 'index']);
    Route::post('/donaciones', [App\Http\Controllers\Api\V1\User\DonacionController::class, 'store']);
    Route::get('/donaciones/{id}/certificado', [App\Http\Controllers\Api\V1\User\DonacionController::class, 'certificado']);
});

// =========================================================================
// API V1 - RUTAS DE ENTIDADES (FUNDACIÓN Y VETERINARIA)
// =========================================================================

Route::middleware(['auth:sanctum'])->prefix('entity')->name('entity.')->group(function () {
    // Rescates
    Route::prefix('rescates')->name('rescates.')->group(function () {
        Route::get('/disponibles', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'disponibles']);
        Route::get('/mis-rescates', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'misRescates']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'show']);
        Route::put('/{id}/aceptar', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'aceptar']);
        Route::put('/{id}/rechazar', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'rechazar']);
        Route::put('/{id}/completar', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'completar']);
        Route::post('/{id}/registrar-mascota', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'registrarMascota']);
        Route::post('/{id}/agregar-fotos', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'agregarFotos']);
        Route::patch('/{id}/estado', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'actualizarEstado']);
    });

    // Mascotas
    Route::prefix('mascotas')->name('mascotas.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'destroy']);
        Route::patch('/{id}/estado', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'actualizarEstado']);
    });

    // Solicitudes de adopción
    Route::prefix('solicitudes')->name('solicitudes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'index']);
        Route::get('/estadisticas', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'estadisticas']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'show']);
        Route::put('/{id}/aprobar', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'aprobar']);
        Route::put('/{id}/rechazar', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'rechazar']);
    });

    Route::prefix('adopciones')->name('adopciones.')->group(function () {
        // SEGUIMIENTOS POST-ADOPCIÓN
        Route::get('/{adopcionId}/seguimientos', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'index']);
        Route::post('/{adopcionId}/seguimientos', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'store']);
        Route::get('/seguimientos/pendientes', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'pendientes']);
        Route::get('/seguimientos/estadisticas', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'estadisticas']);
        Route::get('/seguimientos/{id}', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'show']);
        Route::put('/seguimientos/{id}', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'update']);
        Route::delete('/seguimientos/{id}', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'destroy']);
        Route::patch('/seguimientos/{id}/completar', [App\Http\Controllers\Api\V1\Entity\SeguimientoController::class, 'completar']);
    });
    // Razas
    Route::apiResource('razas', App\Http\Controllers\Api\V1\Entity\RazaController::class);
    Route::get('/razas/especie/{especie}', [App\Http\Controllers\Api\V1\Entity\RazaController::class, 'porEspecie']);
    Route::get('/razas-especies/todas', [App\Http\Controllers\Api\V1\Entity\RazaController::class, 'especies']);

    // Tipos de Vacuna
    Route::apiResource('tipos-vacunas', App\Http\Controllers\Api\V1\Entity\TipoVacunaController::class);
    Route::get('/tipos-vacunas/recomendadas', [App\Http\Controllers\Api\V1\Entity\TipoVacunaController::class, 'recomendadas']);

    // Eventos - CRUD
    Route::apiResource('eventos', EntityEventoController::class);

    // Suscripciones - CRUD (para la entidad)
    Route::apiResource('suscripciones', EntitySuscripcionController::class);
    Route::get('/suscripciones/mascota/{mascotaId}', [EntitySuscripcionController::class, 'porMascota']);
    Route::get('/suscripciones-estadisticas', [EntitySuscripcionController::class, 'estadisticas']);
});
// =========================================================================
// PERFIL ESPECÍFICO PARA FUNDACIONES
// =========================================================================
Route::middleware(['auth:sanctum'])->prefix('fundacion')->name('fundacion.')->group(function () {
    // Perfil completo de fundación (users + fundacion)
    Route::get('/profile', [App\Http\Controllers\Api\V1\Fundacion\FundacionProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\Api\V1\Fundacion\FundacionProfileController::class, 'update']);

    // Datos específicos de fundación
    Route::put('/profile/general', [App\Http\Controllers\Api\V1\Fundacion\FundacionProfileController::class, 'updateGeneralInfo']);
    Route::put('/profile/needs', [App\Http\Controllers\Api\V1\Fundacion\FundacionProfileController::class, 'updateNeeds']);
    Route::put('/profile/schedule', [App\Http\Controllers\Api\V1\Fundacion\FundacionProfileController::class, 'updateSchedule']);
    Route::post('/profile/cover', [App\Http\Controllers\Api\V1\Fundacion\FundacionProfileController::class, 'uploadCoverImage']);
    Route::delete('/profile/cover', [App\Http\Controllers\Api\V1\Fundacion\FundacionProfileController::class, 'deleteCoverImage']);
});

// =========================================================================
// PERFIL ESPECÍFICO PARA VETERINARIAS
// =========================================================================
Route::middleware(['auth:sanctum'])->prefix('veterinaria')->name('veterinaria.')->group(function () {
    // Perfil completo de veterinaria (users + veterinaria)
    Route::get('/profile', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'update']);

    // Datos específicos de veterinaria
    Route::put('/profile/general', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'updateGeneralInfo']);
    Route::put('/profile/services', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'updateServices']);
    Route::put('/profile/schedule', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'updateSchedule']);
    Route::post('/profile/logo', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'uploadLogo']);
    Route::delete('/profile/logo', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'deleteLogo']);
    Route::post('/profile/gallery', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'addGalleryPhotos']);
    Route::delete('/profile/gallery', [App\Http\Controllers\Api\V1\Veterinaria\VeterinariaProfileController::class, 'removeGalleryPhoto']);
});

// =========================================================================
// API V1 - RUTAS DE ADMIN
// =========================================================================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'index']);

    // Notificaciones
    Route::apiResource('notificaciones', App\Http\Controllers\Api\V1\Admin\NotificacionController::class);
    Route::post('/notificaciones/enviar-masivo', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'enviarMasivo']);
    Route::get('/notificaciones/usuario/{userId}', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'porUsuario']);
    Route::patch('/notificaciones/{id}/leida', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'marcarComoLeida']);
    Route::patch('/notificaciones/usuario/{userId}/leidas', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'marcarTodasComoLeidas']);
    Route::get('/notificaciones-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'estadisticas']);

    // Mascotas
    Route::apiResource('mascotas', App\Http\Controllers\Api\V1\Admin\MascotaController::class);
    Route::patch('/mascotas/{id}/destacada', [App\Http\Controllers\Api\V1\Admin\MascotaController::class, 'toggleDestacada']);

    // Seguimientos
    Route::prefix('seguimientos')->name('seguimientos.')->group(function () {
        Route::get('/adopcion/{adopcionId}', [App\Http\Controllers\Api\V1\Admin\SeguimientoController::class, 'index']);
        Route::post('/adopcion/{adopcionId}', [App\Http\Controllers\Api\V1\Admin\SeguimientoController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Admin\SeguimientoController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\V1\Admin\SeguimientoController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\V1\Admin\SeguimientoController::class, 'destroy']);
        Route::get('/estadisticas/{adopcionId}', [App\Http\Controllers\Api\V1\Admin\SeguimientoController::class, 'estadisticas']);
    });

    // Usuarios
    Route::apiResource('usuarios', App\Http\Controllers\Api\V1\Admin\UsuarioController::class);
    Route::patch('/usuarios/{usuario}/estado', [App\Http\Controllers\Api\V1\Admin\UsuarioController::class, 'cambiarEstado']);
    Route::post('/usuarios/{usuario}/verificar-email', [App\Http\Controllers\Api\V1\Admin\UsuarioController::class, 'verificarEmail']);
    Route::get('/usuarios/pendientes/count', [App\Http\Controllers\Api\V1\Admin\UsuarioController::class, 'pendientesCount']);

    // Adopciones
    Route::apiResource('adopciones', App\Http\Controllers\Api\V1\Admin\AdopcionController::class);
    Route::patch('/adopciones/{adopcion}/estado', [App\Http\Controllers\Api\V1\Admin\AdopcionController::class, 'cambiarEstado']);
    Route::get('/adopciones/{adopcion}/seguimientos', [App\Http\Controllers\Api\V1\Admin\AdopcionController::class, 'seguimientos']);
    Route::post('/adopciones/{adopcion}/seguimientos', [App\Http\Controllers\Api\V1\Admin\AdopcionController::class, 'storeSeguimiento']);

    // Solicitudes
    Route::apiResource('solicitudes', App\Http\Controllers\Api\V1\Admin\SolicitudController::class);
    Route::patch('/solicitudes/{id}/estado', [App\Http\Controllers\Api\V1\Admin\SolicitudController::class, 'cambiarEstado']);
    Route::get('/solicitudes-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\SolicitudController::class, 'estadisticas']);

    // Donaciones
    Route::apiResource('donaciones', App\Http\Controllers\Api\V1\Admin\DonacionController::class);
    Route::patch('/donaciones/{donacion}/toggle-publica', [App\Http\Controllers\Api\V1\Admin\DonacionController::class, 'togglePublica']);
    Route::get('/donaciones-reportes/generales', [App\Http\Controllers\Api\V1\Admin\DonacionController::class, 'reporte']);
    Route::get('/donaciones/metodo-pago', [App\Http\Controllers\Api\V1\Admin\DonacionController::class, 'porMetodoPago']);

    // Rescates
    Route::apiResource('rescates', App\Http\Controllers\Api\V1\Admin\RescateController::class);
    Route::post('/rescates/{id}/asignar', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'asignar']);
    Route::patch('/rescates/{id}/estado', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'actualizarEstado']);
    Route::post('/rescates/{id}/fotos', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'agregarFotos']);
    Route::get('/rescates-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'estadisticas']);

    // Reportes
    Route::get('/reportes/generales', [App\Http\Controllers\Api\V1\Admin\ReporteController::class, 'estadisticas']);
    Route::get('/reportes/cercanos', [App\Http\Controllers\Api\V1\Admin\ReporteController::class, 'cercanos']);
    Route::apiResource('reportes', App\Http\Controllers\Api\V1\Admin\ReporteController::class);
    Route::post('/reportes/{reporte}/convertir-rescate', [App\Http\Controllers\Api\V1\Admin\ReporteController::class, 'convertirARescate']);

    // Comentarios
    Route::apiResource('comentarios', App\Http\Controllers\Api\V1\Admin\ComentarioController::class);
    Route::post('/comentarios/accion-masiva', [App\Http\Controllers\Api\V1\Admin\ComentarioController::class, 'masivo']);

    // Razas
    Route::apiResource('razas', App\Http\Controllers\Api\V1\Admin\RazaController::class);
    Route::get('/razas/especie/{especie}', [App\Http\Controllers\Api\V1\Admin\RazaController::class, 'porEspecie']);
    Route::get('/razas-especies/todas', [App\Http\Controllers\Api\V1\Admin\RazaController::class, 'especies']);

    // Tipos de Vacuna
    Route::apiResource('tipos-vacunas', App\Http\Controllers\Api\V1\Admin\TipoVacunaController::class);
    Route::get('/tipos-vacunas/recomendadas', [App\Http\Controllers\Api\V1\Admin\TipoVacunaController::class, 'recomendadas']);
    Route::get('/tipos-vacunas-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\TipoVacunaController::class, 'estadisticas']);

    // Fundaciones
    Route::apiResource('fundaciones', App\Http\Controllers\Api\V1\Admin\FundacionController::class);
    Route::get('/fundaciones/{fundacion}/necesidades', [App\Http\Controllers\Api\V1\Admin\FundacionController::class, 'necesidades']);
    Route::put('/fundaciones/{fundacion}/necesidades', [App\Http\Controllers\Api\V1\Admin\FundacionController::class, 'actualizarNecesidades']);
    Route::get('/fundaciones/cercanas', [App\Http\Controllers\Api\V1\Admin\FundacionController::class, 'cercanas']);

    // Veterinarias
    Route::apiResource('veterinarias', App\Http\Controllers\Api\V1\Admin\VeterinariaController::class);
    Route::get('/veterinarias/cercanas', [App\Http\Controllers\Api\V1\Admin\VeterinariaController::class, 'cercanas']);

    // Eventos - CRUD
    Route::apiResource('eventos', AdminEventoController::class);
    Route::get('/eventos/calendario/data', [AdminEventoController::class, 'getCalendarData']);
    Route::get('/eventos/proximos', [AdminEventoController::class, 'proximos']);

    // Suscripciones - CRUD (admin gestiona todas)
    Route::apiResource('suscripciones', AdminSuscripcionController::class);
    Route::patch('/suscripciones/{id}/cancelar', [AdminSuscripcionController::class, 'cancelar']);
    Route::patch('/suscripciones/{id}/pausar', [AdminSuscripcionController::class, 'pausar']);
    Route::patch('/suscripciones/{id}/reactivar', [AdminSuscripcionController::class, 'reactivar']);
});
