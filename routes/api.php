<?php

use Illuminate\Support\Facades\Route;

// ===== RUTA DE PRUEBA =====
Route::get('/mascotas-test', function() {
    return response()->json([
        'success' => true,
        'message' => 'Ruta de prueba funcionando',
        'data' => [
            'mascotas' => []
        ]
    ]);
});

// =========================================================================
// API V1 - RUTAS PÚBLICAS (SIN AUTENTICACIÓN)
// =========================================================================

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\V1\Auth\LoginController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\Api\V1\Auth\RegisterController::class, 'register']);
    Route::post('/logout', [App\Http\Controllers\Api\V1\Auth\LogoutController::class, 'logout'])
        ->middleware('auth:sanctum');
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

// Rescates - Público (URGENTE - sin auth)
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

// Productos/Tienda - Público
Route::prefix('productos')->name('productos.')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\Public\ProductoController::class, 'index']);
    Route::get('/categoria/{categoriaId}', [App\Http\Controllers\Api\V1\Public\ProductoController::class, 'porCategoria']);
    Route::get('/{id}', [App\Http\Controllers\Api\V1\Public\ProductoController::class, 'show']);
});

// Comentarios - Público (ver), Auth (crear)
Route::prefix('comentarios')->name('comentarios.')->group(function () {
    Route::get('/{entidadTipo}/{entidadId}', [App\Http\Controllers\Api\V1\Public\ComentarioController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\V1\Public\ComentarioController::class, 'store'])
        ->middleware('auth:sanctum');
});

// =========================================================================
// API V1 - RUTAS DE USUARIO (REQUIEREN AUTENTICACIÓN)
// =========================================================================

Route::middleware(['auth:sanctum'])->prefix('user')->name('user.')->group(function () {
    // Perfil
    Route::get('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\Api\V1\User\ProfileController::class, 'update']);

    // Solicitudes de adopción del usuario
    Route::get('/solicitudes', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'index']);
    Route::post('/solicitudes/adopcion', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'storeAdopcion']);
    Route::get('/solicitudes/{id}', [App\Http\Controllers\Api\V1\User\SolicitudController::class, 'show']);

    // Carrito y pedidos
    Route::prefix('carrito')->name('carrito.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\User\CarritoController::class, 'index']);
        Route::post('/agregar/{productoId}', [App\Http\Controllers\Api\V1\User\CarritoController::class, 'agregar']);
        Route::put('/actualizar/{productoId}', [App\Http\Controllers\Api\V1\User\CarritoController::class, 'actualizar']);
        Route::delete('/eliminar/{productoId}', [App\Http\Controllers\Api\V1\User\CarritoController::class, 'eliminar']);
    });

    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\V1\User\PedidoController::class, 'index']);
        Route::post('/checkout', [App\Http\Controllers\Api\V1\User\PedidoController::class, 'checkout']);
        Route::get('/{id}', [App\Http\Controllers\Api\V1\User\PedidoController::class, 'show']);
        Route::post('/{id}/cancelar', [App\Http\Controllers\Api\V1\User\PedidoController::class, 'cancelar']);
    });

    // Donaciones del usuario
    Route::get('/donaciones', [App\Http\Controllers\Api\V1\User\DonacionController::class, 'index']);
    Route::post('/donaciones', [App\Http\Controllers\Api\V1\User\DonacionController::class, 'store']);
});

// =========================================================================
// API V1 - RUTAS DE ADMIN (REQUIEREN AUTENTICACIÓN + ROL ADMIN)
// =========================================================================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'index']);

    // ===== MASCOTAS =====
    Route::apiResource('mascotas', App\Http\Controllers\Api\V1\Admin\MascotaController::class);
    Route::delete('/mascotas/{mascota}/foto-galeria', [App\Http\Controllers\Api\V1\Admin\MascotaController::class, 'eliminarFotoGaleria']);

    // ===== USUARIOS =====
    Route::apiResource('usuarios', App\Http\Controllers\Api\V1\Admin\UsuarioController::class);
    Route::patch('/usuarios/{usuario}/estado', [App\Http\Controllers\Api\V1\Admin\UsuarioController::class, 'cambiarEstado']);
    Route::post('/usuarios/{usuario}/verificar-email', [App\Http\Controllers\Api\V1\Admin\UsuarioController::class, 'verificarEmail']);

    // ===== ADOPCIONES =====
    Route::apiResource('adopciones', App\Http\Controllers\Api\V1\Admin\AdopcionController::class);
    Route::patch('/adopciones/{adopcion}/estado', [App\Http\Controllers\Api\V1\Admin\AdopcionController::class, 'cambiarEstado']);
    Route::get('/adopciones/{adopcion}/seguimientos', [App\Http\Controllers\Api\V1\Admin\AdopcionController::class, 'seguimientos']);
    Route::post('/adopciones/{adopcion}/seguimientos', [App\Http\Controllers\Api\V1\Admin\AdopcionController::class, 'storeSeguimiento']);

    // ===== SOLICITUDES =====
    Route::apiResource('solicitudes', App\Http\Controllers\Api\V1\Admin\SolicitudController::class);
    Route::patch('/solicitudes/{id}/status', [App\Http\Controllers\Api\V1\Admin\SolicitudController::class, 'updateStatus']);
    Route::get('/solicitudes-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\SolicitudController::class, 'estadisticas']);

    // ===== DONACIONES =====
    Route::apiResource('donaciones', App\Http\Controllers\Api\V1\Admin\DonacionController::class);
    Route::patch('/donaciones/{donacion}/toggle-publica', [App\Http\Controllers\Api\V1\Admin\DonacionController::class, 'togglePublica']);
    Route::get('/donaciones-reportes/generales', [App\Http\Controllers\Api\V1\Admin\DonacionController::class, 'reporte']);

    // ===== RESCATES =====
    Route::apiResource('rescates', App\Http\Controllers\Api\V1\Admin\RescateController::class);
    Route::post('/rescates/{rescate}/completar', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'completar']);
    Route::post('/rescates/{rescate}/asignar-entidad', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'asignarEntidad']);
    Route::get('/rescates-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\RescateController::class, 'estadisticas']);

    // ===== REPORTES =====
    Route::get('/reportes/generales', [App\Http\Controllers\Api\V1\Admin\ReporteController::class, 'estadisticas']);
    Route::get('/reportes/cercanos', [App\Http\Controllers\Api\V1\Admin\ReporteController::class, 'cercanos']);
    Route::apiResource('reportes', App\Http\Controllers\Api\V1\Admin\ReporteController::class);
    Route::post('/reportes/{reporte}/convertir-rescate', [App\Http\Controllers\Api\V1\Admin\ReporteController::class, 'convertirARescate']);

    // ===== COMENTARIOS =====
    Route::apiResource('comentarios', App\Http\Controllers\Api\V1\Admin\ComentarioController::class);
    Route::post('/comentarios/accion-masiva', [App\Http\Controllers\Api\V1\Admin\ComentarioController::class, 'masivo']);

    // ===== PRODUCTOS =====
    Route::apiResource('productos', App\Http\Controllers\Api\V1\Admin\ProductoController::class);
    Route::patch('/productos/{producto}/estado', [App\Http\Controllers\Api\V1\Admin\ProductoController::class, 'cambiarEstado']);
    Route::post('/productos/{producto}/stock', [App\Http\Controllers\Api\V1\Admin\ProductoController::class, 'actualizarStock']);
    Route::get('/productos-stock/bajo', [App\Http\Controllers\Api\V1\Admin\ProductoController::class, 'stockBajo']);

    // ===== PEDIDOS =====
    Route::apiResource('pedidos', App\Http\Controllers\Api\V1\Admin\PedidoController::class);
    Route::patch('/pedidos/{pedido}/estado', [App\Http\Controllers\Api\V1\Admin\PedidoController::class, 'cambiarEstado']);
    Route::get('/pedidos-reportes/generales', [App\Http\Controllers\Api\V1\Admin\PedidoController::class, 'reporte']);

    // ===== CATEGORÍAS =====
    Route::apiResource('categorias', App\Http\Controllers\Api\V1\Admin\CategoriaController::class);
    Route::patch('/categorias/{categoria}/toggle', [App\Http\Controllers\Api\V1\Admin\CategoriaController::class, 'toggleActivo']);
    Route::get('/categorias-arbol', [App\Http\Controllers\Api\V1\Admin\CategoriaController::class, 'arbol']);
    Route::get('/categorias/para-select', [App\Http\Controllers\Api\V1\Admin\CategoriaController::class, 'paraSelect']);

    // ===== TIENDAS =====
    Route::apiResource('tiendas', App\Http\Controllers\Api\V1\Admin\TiendaController::class);
    Route::get('/tiendas/{tienda}/productos', [App\Http\Controllers\Api\V1\Admin\TiendaController::class, 'productos']);

    // ===== RAZAS =====
    Route::apiResource('razas', App\Http\Controllers\Api\V1\Admin\RazaController::class);
    Route::get('/razas/especie/{especie}', [App\Http\Controllers\Api\V1\Admin\RazaController::class, 'porEspecie']);
    Route::get('/razas-especies/todas', [App\Http\Controllers\Api\V1\Admin\RazaController::class, 'especies']);

    // ===== TIPOS DE VACUNA =====
    Route::apiResource('tipos-vacunas', App\Http\Controllers\Api\V1\Admin\TipoVacunaController::class);
    Route::get('/tipos-vacunas/recomendadas', [App\Http\Controllers\Api\V1\Admin\TipoVacunaController::class, 'recomendadas']);
    Route::get('/tipos-vacunas-estadisticas/generales', [App\Http\Controllers\Api\V1\Admin\TipoVacunaController::class, 'estadisticas']);

    // ===== FUNDACIONES =====
    Route::apiResource('fundaciones', App\Http\Controllers\Api\V1\Admin\FundacionController::class);
    Route::get('/fundaciones/{fundacion}/necesidades', [App\Http\Controllers\Api\V1\Admin\FundacionController::class, 'necesidades']);
    Route::put('/fundaciones/{fundacion}/necesidades', [App\Http\Controllers\Api\V1\Admin\FundacionController::class, 'actualizarNecesidades']);

    // ===== VETERINARIAS =====
    Route::apiResource('veterinarias', App\Http\Controllers\Api\V1\Admin\VeterinariaController::class);
    Route::get('/veterinarias/cercanas', [App\Http\Controllers\Api\V1\Admin\VeterinariaController::class, 'cercanas']);

    // ===== EVENTOS =====
    Route::apiResource('eventos', App\Http\Controllers\Api\V1\Admin\EventoController::class);
    Route::get('/eventos/calendario/data', [App\Http\Controllers\Api\V1\Admin\EventoController::class, 'calendarData']);
    Route::get('/eventos/proximos', [App\Http\Controllers\Api\V1\Admin\EventoController::class, 'proximos']);
});
