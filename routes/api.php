<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
// API V1 - RUTAS PÚBLICAS (SIN AUTENTICACIÓN)
// =========================================================================

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
Route::get('/mascotas', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'index']);
Route::get('/mascotas/{id}', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'show']);
Route::get('/mascotas/especie/{especie}', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'porEspecie']);
Route::get('/mascotas/fundacion/{fundacionId}', [App\Http\Controllers\Api\V1\Public\MascotaController::class, 'porFundacion']);

// Adopciones - Público
Route::prefix('adopciones')->name('adopciones.')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'index']);
    Route::get('/disponibles', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'disponibles']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\Public\AdopcionController::class, 'show']);
});

// Rescates - Público
Route::prefix('rescates')->name('rescates.')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\Public\RescateController::class, 'index']);
    Route::post('/reportar', [App\Http\Controllers\Api\V1\Public\RescateController::class, 'reportar']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\Public\RescateController::class, 'show']);
});

// Fundaciones - Público
Route::apiResource('fundaciones', App\Http\Controllers\Api\V1\Public\FundacionController::class)
    ->only(['index', 'show']);

// Veterinarias - Público
Route::apiResource('veterinarias', App\Http\Controllers\Api\V1\Public\VeterinariaController::class)
    ->only(['index', 'show']);
Route::get('/veterinarias/urgencias/mapa', [App\Http\Controllers\Api\V1\Public\VeterinariaController::class, 'mapa']);

// Eventos - Público
Route::apiResource('eventos', App\Http\Controllers\Api\V1\Public\EventoController::class)
    ->only(['index', 'show']);
Route::get('/eventos/calendario/data', [App\Http\Controllers\Api\V1\Public\EventoController::class, 'calendario']);

// =========================================================================
// API V1 - RUTAS DE USUARIO (REQUIEREN AUTENTICACIÓN)
// =========================================================================

Route::middleware(['auth:sanctum'])->prefix('user')->name('user.')->group(function () {
    // Perfil
    Route::get('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'update']);
    Route::post('/profile/change-password', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'changePassword']);
    Route::delete('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'destroy']);

    // Solicitudes de adopción del usuario
    Route::get('/solicitudes', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'index']);
    Route::post('/solicitudes/adopcion', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'storeAdopcion']);
    Route::get('/solicitudes/{id}', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'show']);

    // Donaciones del usuario
    Route::get('/donaciones', [App\Http\Controllers\Api\V1\User\DonacionController::class, 'index']);
    Route::post('/donaciones', [App\Http\Controllers\Api\V1\User\DonacionController::class, 'store']);
});

// =========================================================================
// API V1 - RUTAS DE ENTIDADES (FUNDACIÓN Y VETERINARIA)
// =========================================================================

Route::middleware(['auth:sanctum'])->prefix('entity')->name('entity.')->group(function () {
    // Rescates para entidades
    Route::prefix('rescates')->name('rescates.')->group(function () {
        Route::get('/disponibles', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'disponibles']);
        Route::get('/mis-rescates', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'misRescates']);
        Route::put('/{id}/aceptar', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'aceptar']);
        Route::put('/{id}/rechazar', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'rechazar']);
        Route::post('/{id}/registrar-mascota', [App\Http\Controllers\Api\V1\Entity\RescateController::class, 'registrarMascota']);
    });

    // Mascotas de la entidad
    Route::prefix('mascotas')->name('mascotas.')->group(function () {
        Route::get('/mascotas-form-data', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'createFormData']);
        Route::get('/', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\V1\Entity\MascotaController::class, 'destroy']);

    });

    // Solicitudes de adopción para la entidad
    Route::prefix('solicitudes')->name('solicitudes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'show']);
        Route::put('/{id}/aprobar', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'aprobar']);
        Route::put('/{id}/rechazar', [App\Http\Controllers\Api\V1\Entity\SolicitudController::class, 'rechazar']);
    });
});

// =========================================================================
// API V1 - RUTAS DE ADMIN (REQUIEREN AUTENTICACIÓN + ROL ADMIN)
// =========================================================================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'index']);

    // Notificaciones
    Route::apiResource('notificaciones', App\Http\Controllers\Api\V1\Admin\NotificacionController::class);
    Route::post('/notificaciones/enviar-masivo', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'enviarMasivo']);
    Route::get('/notificaciones/usuario/{userId}', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'porUsuario']);
    Route::get('/notificaciones-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\NotificacionController::class, 'estadisticas']);

    // Mascotas
    Route::apiResource('mascotas', App\Http\Controllers\Api\V1\Admin\MascotaController::class);
    Route::delete('/mascotas/{mascota}/foto-galeria', [App\Http\Controllers\Api\V1\Admin\MascotaController::class, 'eliminarFotoGaleria']);

    // Seguimientos de adopciones
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
    Route::patch('/solicitudes/{id}/status', [App\Http\Controllers\Api\V1\Admin\SolicitudController::class, 'updateStatus']);
    Route::get('/solicitudes-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\SolicitudController::class, 'estadisticas']);

    // Donaciones
    Route::apiResource('donaciones', App\Http\Controllers\Api\V1\Admin\DonacionController::class);
    Route::patch('/donaciones/{donacion}/toggle-publica', [App\Http\Controllers\Api\V1\Admin\DonacionController::class, 'togglePublica']);
    Route::get('/donaciones-reportes/generales', [App\Http\Controllers\Api\V1\Admin\DonacionController::class, 'reporte']);

    // Rescates - ADMIN
    Route::apiResource('rescates', App\Http\Controllers\Api\V1\Admin\RescateController::class);
    Route::post('/rescates/{id}/asignar', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'asignar']);
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

    // Veterinarias
    Route::apiResource('veterinarias', App\Http\Controllers\Api\V1\Admin\VeterinariaController::class);
    Route::get('/veterinarias/cercanas', [App\Http\Controllers\Api\V1\Admin\VeterinariaController::class, 'cercanas']);

    // Eventos
    Route::apiResource('eventos', App\Http\Controllers\Api\V1\Admin\EventoController::class);
    Route::get('/eventos/calendario/data', [App\Http\Controllers\Api\V1\Admin\EventoController::class, 'calendarData']);
    Route::get('/eventos/proximos', [App\Http\Controllers\Api\V1\Admin\EventoController::class, 'proximos']);
});
