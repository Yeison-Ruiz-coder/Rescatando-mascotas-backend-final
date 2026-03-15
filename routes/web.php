<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// =========================================================================
// RUTAS PÚBLICAS (SIN AUTENTICACIÓN - ACCESO LIBRE)
// =========================================================================

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Public\CarritoController;
use App\Http\Controllers\Public\PedidoController as PublicPedidoController;
use App\Http\Controllers\Public\MascotaController as PublicMascotaController;
use App\Http\Controllers\Public\AdopcionController as PublicAdopcionController;
use App\Http\Controllers\Public\EventoController as PublicEventoController;
use App\Http\Controllers\Public\DonacionController as PublicDonacionController;
use App\Http\Controllers\Public\VeterinariaController as PublicVeterinariaController;
use App\Http\Controllers\Public\FundacionController as PublicFundacionController;
use App\Http\Controllers\Public\RescateController as PublicRescateController;
use App\Http\Controllers\Public\ProductoController as PublicProductoController;
use App\Http\Controllers\Public\ComentarioController as PublicComentarioController;
use App\Http\Controllers\Public\TiendaController as PublicTiendaController;

// ✅ PÁGINA DE INICIO - PÚBLICA
Route::get('/', [HomeController::class, 'index'])->name('inicio');

// ✅ NOSOTROS - PÚBLICA
Route::get('/nosotros', fn() => view('public.nosotros'))->name('nosotros');

// ✅ MASCOTAS - PÚBLICO (ver listado y detalle)
Route::prefix('mascotas')->name('public.mascotas.')->group(function () {
    Route::get('/', [PublicMascotaController::class, 'index'])->name('index');
    Route::get('/{id}', [PublicMascotaController::class, 'show'])->name('show');
});

// ✅ ADOPCIONES - PÚBLICO (ver), PRIVADO (solicitar)
Route::prefix('adopciones')->name('public.adopciones.')->group(function () {
    // Público
    Route::get('/', [PublicAdopcionController::class, 'index'])->name('index');
    Route::get('/mascota/{id}', [PublicAdopcionController::class, 'show'])->name('show');
    Route::get('/verificar-disponibilidad/{id}', [PublicAdopcionController::class, 'verificarDisponibilidad'])->name('verificar-disponibilidad');

    // Requiere autenticación (responsabilidad)
    Route::middleware(['auth'])->group(function () {
        Route::get('/solicitar/{id}', [PublicAdopcionController::class, 'solicitar'])->name('solicitar');
        Route::post('/solicitar', [PublicAdopcionController::class, 'solicitarStore'])->name('solicitar.store');
        Route::get('/solicitud-exitosa/{id}', [PublicAdopcionController::class, 'solicitudExitosa'])->name('solicitud-exitosa');
        Route::get('/mis-solicitudes', [PublicAdopcionController::class, 'misSolicitudes'])->name('mis-solicitudes');
        Route::get('/en-proceso', [PublicAdopcionController::class, 'enProceso'])->name('en-proceso');
        Route::get('/solicitud/{id}', [PublicAdopcionController::class, 'solicitudDetalle'])->name('solicitud-detalle');
    });
});

// ✅ RESCATES - PÚBLICO (reportar emergencia NO necesita login)
Route::prefix('rescates')->name('public.rescates.')->group(function () {
    Route::get('/', [PublicRescateController::class, 'index'])->name('index');
    Route::get('/create', [PublicRescateController::class, 'create'])->name('create'); // Formulario público
    Route::post('/', [PublicRescateController::class, 'guardarReporte'])->name('store'); // ¡PÚBLICO! (sin middleware)
    Route::get('/reporte-exitoso/{id}', [PublicRescateController::class, 'reporteExitoso'])->name('reporte-exitoso');
    Route::get('/{id}', [PublicRescateController::class, 'show'])->name('show');
});

// ✅ EVENTOS - PÚBLICO
Route::prefix('eventos')->name('public.eventos.')->group(function () {
    Route::get('/', [PublicEventoController::class, 'index'])->name('index');
    Route::get('/{evento}', [PublicEventoController::class, 'show'])->name('show');
    Route::get('/calendario/vista', [PublicEventoController::class, 'calendario'])->name('calendario');
});

// ✅ DONACIONES - PÚBLICO (ver), PRIVADO (donar con registro)
Route::prefix('donaciones')->name('public.donaciones.')->group(function () {
    // Público
    Route::get('/', [PublicDonacionController::class, 'index'])->name('index');

    // Requiere autenticación (responsabilidad financiera)
    Route::middleware(['auth'])->group(function () {
        Route::get('/crear', [PublicDonacionController::class, 'create'])->name('create');
        Route::post('/', [PublicDonacionController::class, 'store'])->name('store');
        Route::get('/{id}', [PublicDonacionController::class, 'show'])->name('show');
    });
});

// ✅ VETERINARIAS - PÚBLICO
Route::prefix('veterinarias')->name('public.veterinarias.')->group(function () {
    Route::get('/', [PublicVeterinariaController::class, 'index'])->name('index');
    Route::get('/{id}', [PublicVeterinariaController::class, 'show'])->name('show');
    Route::get('/mapa/ver', [PublicVeterinariaController::class, 'mapa'])->name('mapa');
});

// ✅ FUNDACIONES - PÚBLICO
Route::prefix('fundaciones')->name('public.fundaciones.')->group(function () {
    Route::get('/', [PublicFundacionController::class, 'index'])->name('index');
    Route::get('/{id}', [PublicFundacionController::class, 'show'])->name('show');
});

// ✅ TIENDA - PÚBLICO (ver productos), PRIVADO (comprar)
Route::prefix('tienda')->name('public.tienda.')->group(function () {
    // Público
    Route::get('/', [PublicTiendaController::class, 'index'])->name('index');
    Route::get('/vendedor/{vendedorId}', [PublicTiendaController::class, 'porVendedor'])->name('vendedor');

    // Productos (público)
    Route::get('/producto/{id}', [PublicProductoController::class, 'show'])->name('producto.show');
});

// ✅ PRODUCTOS - PÚBLICO (ver)
Route::prefix('productos')->name('public.productos.')->group(function () {
    Route::get('/', [PublicProductoController::class, 'index'])->name('index');
    Route::get('/categoria/{categoriaId}', [PublicProductoController::class, 'porCategoria'])->name('categoria');
    Route::get('/{id}', [PublicProductoController::class, 'show'])->name('show');
});

// ✅ COMENTARIOS - PÚBLICO (ver), PRIVADO (publicar - opcional)
Route::prefix('comentarios')->name('public.comentarios.')->group(function () {
    // Público
    Route::get('/{entidadTipo}/{entidadId}', [PublicComentarioController::class, 'index'])->name('index');

    // Publicar comentario (puede ser con o sin login - tú decides)
    // Opción 1: Con login (más control)
    Route::middleware(['auth'])->post('/', [PublicComentarioController::class, 'store'])->name('store');

    // Opción 2: Sin login (más participación) - DESCOMENTAR si prefieres
    // Route::post('/', [PublicComentarioController::class, 'store'])->name('store');
});

// =========================================================================
// RUTAS QUE REQUIEREN AUTENTICACIÓN (SIEMPRE)
// =========================================================================

Route::middleware(['auth'])->group(function () {

    // ✅ PERFIL DE USUARIO
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ CARRITO DE COMPRAS (requiere usuario)
    Route::prefix('carrito')->name('public.carrito.')->group(function () {
        Route::get('/', [CarritoController::class, 'index'])->name('index');
        Route::post('/agregar/{producto}', [CarritoController::class, 'agregar'])->name('agregar');
        Route::put('/actualizar/{productoId}', [CarritoController::class, 'actualizar'])->name('actualizar');
        Route::delete('/eliminar/{productoId}', [CarritoController::class, 'eliminar'])->name('eliminar');
    });

    // ✅ PEDIDOS (requiere usuario)
    Route::prefix('pedidos')->name('public.pedidos.')->group(function () {
        Route::get('/', [PublicPedidoController::class, 'index'])->name('index');
        Route::get('/checkout', [PublicPedidoController::class, 'checkout'])->name('checkout');
        Route::post('/procesar', [PublicPedidoController::class, 'procesar'])->name('procesar');
        Route::get('/{id}', [PublicPedidoController::class, 'show'])->name('show');
        Route::post('/{id}/cancelar', [PublicPedidoController::class, 'cancelar'])->name('cancelar');
    });
});

// =========================================================================
// RUTAS DE ADMIN (tus rutas originales - con middleware admin)
// =========================================================================

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MascotaController;
use App\Http\Controllers\Admin\AdopcionController;
use App\Http\Controllers\Admin\SolicitudController as AdminSolicitudController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\DonacionController;
use App\Http\Controllers\Admin\RescateController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VeterinariaController;
use App\Http\Controllers\Admin\FundacionController;
use App\Http\Controllers\Admin\ComentarioController as AdminComentarioController;
use App\Http\Controllers\Admin\NotificacionController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\RazaController;
use App\Http\Controllers\Admin\TipoVacunaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\TiendaController as AdminTiendaController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard de admin
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');

    // CONFIGURACIÓN
    Route::view('/configuracion', 'admin.configuracion.index')->name('configuracion');

    // MASCOTAS
    Route::resource('mascotas', MascotaController::class);
    Route::delete('/mascotas/{mascota}/foto-galeria', [MascotaController::class, 'eliminarFotoGaleria'])->name('mascotas.eliminar-foto');

    // ADOPCIONES
    Route::resource('adopciones', AdopcionController::class);
    Route::patch('/adopciones/{adopcion}/estado', [AdopcionController::class, 'cambiarEstado'])->name('adopciones.estado');
    Route::get('/adopciones/{adopcion}/seguimientos', [AdopcionController::class, 'seguimientos'])->name('adopciones.seguimientos');
    Route::post('/adopciones/{adopcion}/seguimientos', [AdopcionController::class, 'storeSeguimiento'])->name('adopciones.seguimientos.store');

    // SOLICITUDES
    Route::resource('solicitudes', AdminSolicitudController::class);
    Route::patch('/solicitudes/{id}/status', [AdminSolicitudController::class, 'updateStatus'])->name('solicitudes.update-status');

    // EVENTOS
    Route::resource('eventos', EventoController::class);
    Route::get('/eventos/calendar/vista', [EventoController::class, 'calendar'])->name('eventos.calendar');
    Route::get('/eventos/calendar/data', [EventoController::class, 'calendarData'])->name('eventos.calendar.data');

    // DONACIONES
    Route::resource('donaciones', DonacionController::class);
    Route::patch('/donaciones/{donacion}/toggle-publica', [DonacionController::class, 'togglePublica'])->name('donaciones.toggle-publica');
    Route::get('/donaciones-reportes/generales', [DonacionController::class, 'reporte'])->name('donaciones.reporte');

    // RESCATES
    Route::resource('rescates', RescateController::class);
    Route::post('/rescates/{rescate}/completar', [RescateController::class, 'completar'])->name('rescates.completar');

    // USUARIOS
    Route::resource('usuarios', UsuarioController::class);
    Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');
    Route::post('/usuarios/{usuario}/verificar-email', [UsuarioController::class, 'verificarEmail'])->name('usuarios.verificar-email');

    // VETERINARIAS
    Route::resource('veterinarias', VeterinariaController::class);

    // FUNDACIONES
    Route::resource('fundaciones', FundacionController::class);
    Route::get('/fundaciones/{fundacion}/necesidades', [FundacionController::class, 'necesidades'])->name('fundaciones.necesidades');

    // COMENTARIOS (admin)
    Route::resource('comentarios', AdminComentarioController::class);
    Route::post('/comentarios/accion-masiva', [AdminComentarioController::class, 'masivo'])->name('comentarios.masivo');

    // NOTIFICACIONES
    Route::resource('notificaciones', NotificacionController::class);
    Route::post('/notificaciones/enviar-masivo', [NotificacionController::class, 'enviarMasivo'])->name('notificaciones.enviar-masivo');

    // TIPOS DE VACUNAS
    Route::resource('tipos-vacunas', TipoVacunaController::class);
    Route::get('/tipos-vacunas/recomendadas', [TipoVacunaController::class, 'recomendadas'])->name('tipos-vacunas.recomendadas');

    // RAZAS
    Route::resource('razas', RazaController::class);
    Route::get('/razas/especie/{especie}', [RazaController::class, 'porEspecie'])->name('razas.por-especie');

    // REPORTES
    Route::get('/reportes/generales', [ReporteController::class, 'generales'])->name('reportes.generales');
    Route::get('/reportes/exportar/{tipo}', [ReporteController::class, 'exportar'])->name('reportes.exportar');
    Route::resource('reportes', ReporteController::class);
    Route::post('/reportes/{reporte}/convertir-rescate', [ReporteController::class, 'convertirARescate'])->name('reportes.convertir-rescate');

    // TIENDAS
    Route::resource('tiendas', AdminTiendaController::class);
    Route::get('/tiendas/{tienda}/ventas', [AdminTiendaController::class, 'ventas'])->name('tiendas.ventas');
    Route::get('/tiendas/{tienda}/inventario', [AdminTiendaController::class, 'inventario'])->name('tiendas.inventario');

    // PRODUCTOS
    Route::resource('productos', ProductoController::class);
    Route::patch('/productos/{producto}/estado', [ProductoController::class, 'cambiarEstado'])->name('productos.estado');
    Route::post('/productos/{producto}/stock', [ProductoController::class, 'actualizarStock'])->name('productos.stock');
    Route::get('/productos-stock/bajo', [ProductoController::class, 'stockBajo'])->name('productos.stock-bajo');

    // PEDIDOS
    Route::resource('pedidos', PedidoController::class);
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'cambiarEstado'])->name('pedidos.estado');
    Route::get('/pedidos-reportes/generales', [PedidoController::class, 'reporte'])->name('pedidos.reporte');

    // CATEGORÍAS
    Route::resource('categorias', CategoriaController::class);
    Route::patch('/categorias/{categoria}/toggle', [CategoriaController::class, 'toggleActivo'])->name('categorias.toggle');
    Route::get('/categorias-arbol/visual', [CategoriaController::class, 'arbol'])->name('categorias.arbol');
});

// =========================================================================
// RUTAS DE POSTS Y TRADUCCIÓN (públicas)
// =========================================================================

Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
Route::post('posts', [PostController::class, 'store'])->name('posts.store');
Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::get('locale/{locale}', function ($locale) {
    if (in_array($locale, ['es', 'en'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');

// =========================================================================
// RUTAS DE AUTENTICACIÓN (Breeze)
// =========================================================================
require __DIR__ . '/auth.php';
