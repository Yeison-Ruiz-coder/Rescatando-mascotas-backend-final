<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Services\SuscripcionService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SuscripcionController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected SuscripcionService $suscripcionService;

    public function __construct(SuscripcionService $suscripcionService)
    {
        $this->suscripcionService = $suscripcionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['user_id', 'mascota_id', 'estado']);
        $perPage = $request->get('per_page', 15);

        $suscripciones = $this->suscripcionService->getAll($filters, $perPage);

        return $this->successResponse($suscripciones, 'Suscripciones obtenidas exitosamente');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:1',
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'required|in:activo,pausado,cancelado,finalizado,pendiente',
        ]);

        try {
            $suscripcion = $this->runInTransaction(
                fn() => $this->suscripcionService->create($validated),
                'Error al crear suscripción'
            );

            return $this->successResponse($suscripcion->load(['user', 'mascota']), 'Suscripción creada exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la suscripción', $e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $suscripcion = $this->suscripcionService->findById($id);
            return $this->successResponse($suscripcion, 'Suscripción obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'monto_mensual' => 'sometimes|numeric|min:1',
            'frecuencia' => 'sometimes|in:unica,mensual,trimestral,anual',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'sometimes|in:activo,pausado,cancelado,finalizado,pendiente',
        ]);

        try {
            $suscripcion = $this->runInTransaction(
                fn() => $this->suscripcionService->update($id, $validated),
                'Error al actualizar suscripción'
            );

            return $this->successResponse($suscripcion, 'Suscripción actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la suscripción', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->suscripcionService->delete($id),
                'Error al eliminar suscripción'
            );

            return $this->successResponse(null, 'Suscripción eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la suscripción', $e->getMessage(), 500);
        }
    }

    public function cancelar(int $id)
    {
        try {
            $suscripcion = $this->runInTransaction(
                fn() => $this->suscripcionService->cancelar($id),
                'Error al cancelar suscripción'
            );

            return $this->successResponse($suscripcion, 'Suscripción cancelada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cancelar la suscripción', $e->getMessage(), 500);
        }
    }

    public function pausar(int $id)
    {
        try {
            $suscripcion = $this->runInTransaction(
                fn() => $this->suscripcionService->pausar($id),
                'Error al pausar suscripción'
            );

            return $this->successResponse($suscripcion, 'Suscripción pausada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al pausar la suscripción', $e->getMessage(), 500);
        }
    }

    public function reactivar(int $id)
    {
        try {
            $suscripcion = $this->runInTransaction(
                fn() => $this->suscripcionService->reactivar($id),
                'Error al reactivar suscripción'
            );

            return $this->successResponse($suscripcion, 'Suscripción reactivada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al reactivar la suscripción', $e->getMessage(), 500);
        }
    }

    /**
     * Estadísticas completas para el dashboard
     * GET /api/admin/suscripciones/estadisticas
     */
    public function estadisticas()
    {
        try {
            // ✅ Estadísticas por estado
            $porEstado = DB::table('suscripciones')
                ->select('estado', DB::raw('count(*) as total'))
                ->groupBy('estado')
                ->pluck('total', 'estado')
                ->toArray();

            // ✅ Asegurar que todos los estados existan
            $estados = ['activo', 'pendiente', 'cancelado', 'inactivo', 'finalizado'];
            foreach ($estados as $estado) {
                if (!isset($porEstado[$estado])) {
                    $porEstado[$estado] = 0;
                }
            }

            // ✅ Ingresos totales
            $ingresos = DB::table('suscripciones')
                ->select(
                    DB::raw('SUM(monto_mensual) as total'),
                    DB::raw('SUM(CASE WHEN estado = "activo" THEN monto_mensual ELSE 0 END) as mensual'),
                    DB::raw('SUM(CASE WHEN estado = "activo" THEN monto_mensual * 12 ELSE 0 END) as anual')
                )
                ->first();

            // ✅ Top mascotas (con join)
            $topMascotas = DB::table('suscripciones')
                ->join('mascotas', 'suscripciones.mascota_id', '=', 'mascotas.id')
                ->select(
                    'mascotas.nombre_mascota as nombre',
                    DB::raw('count(*) as count'),
                    DB::raw('SUM(suscripciones.monto_mensual) as ingresos'),
                    DB::raw('AVG(suscripciones.monto_mensual) as promedio')
                )
                ->groupBy('mascotas.id', 'mascotas.nombre_mascota')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // ✅ Top usuarios (con join)
            $topUsuarios = DB::table('suscripciones')
                ->join('users', 'suscripciones.user_id', '=', 'users.id')
                ->select(
                    'users.name as nombre',
                    DB::raw('count(*) as count'),
                    DB::raw('SUM(suscripciones.monto_mensual) as ingresos'),
                    DB::raw('AVG(suscripciones.monto_mensual) as promedio')
                )
                ->groupBy('users.id', 'users.name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // ✅ Distribución mensual (últimos 12 meses)
            $distribucionMensual = DB::table('suscripciones')
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                    DB::raw('SUM(monto_mensual) as valor')
                )
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('mes')
                ->orderBy('mes', 'asc')
                ->get()
                ->map(function ($item) {
                    $meses = [
                        '01' => 'Ene',
                        '02' => 'Feb',
                        '03' => 'Mar',
                        '04' => 'Abr',
                        '05' => 'May',
                        '06' => 'Jun',
                        '07' => 'Jul',
                        '08' => 'Ago',
                        '09' => 'Sep',
                        '10' => 'Oct',
                        '11' => 'Nov',
                        '12' => 'Dic'
                    ];
                    $parts = explode('-', $item->mes);
                    $item->mes = $meses[$parts[1]] . ' ' . $parts[0];
                    return $item;
                });

            // ✅ Total de suscripciones
            $total = Suscripcion::count();

            return $this->successResponse([
                'total' => $total,
                'por_estado' => [
                    'activas' => $porEstado['activo'] ?? 0,
                    'pendientes' => $porEstado['pendiente'] ?? 0,
                    'canceladas' => $porEstado['cancelado'] ?? 0,
                    'inactivas' => $porEstado['inactivo'] ?? 0,
                    'finalizadas' => $porEstado['finalizado'] ?? 0,
                ],
                'ingresos' => [
                    'total' => $ingresos->total ?? 0,
                    'mensual' => $ingresos->mensual ?? 0,
                    'anual' => $ingresos->anual ?? 0,
                ],
                'top_mascotas' => $topMascotas,
                'top_usuarios' => $topUsuarios,
                'promedio_por_suscripcion' => $total > 0
                    ? round(($ingresos->total ?? 0) / $total, 2)
                    : 0,
                'distribucion_mensual' => $distribucionMensual,
            ], 'Estadísticas obtenidas');
        } catch (\Exception $e) {
            Log::error('Error en estadisticas:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), null, 500);
        }
    }
}
