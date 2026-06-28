<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Mascota;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SuscripcionController extends Controller
{
    use ApiResponses;

    /**
     * Obtener las mascotas de la fundación
     */
    private function getMascotasIds()
    {
        $user = Auth::user();

        if ($user->tipo === 'fundacion') {
            $fundacion = $user->fundacion;
            if (!$fundacion) {
                throw new \Exception('Perfil de fundación no encontrado');
            }
            return Mascota::where('fundacion_id', $fundacion->id)->pluck('id');
        }

        if ($user->tipo === 'veterinaria') {
            $veterinaria = $user->veterinaria;
            if (!$veterinaria) {
                throw new \Exception('Perfil de veterinaria no encontrado');
            }
            return Mascota::where('veterinaria_id', $veterinaria->id)->pluck('id');
        }

        throw new \Exception('Tipo de usuario no válido');
    }

    /**
     * Obtener todas las suscripciones de la entidad
     * GET /api/entity/suscripciones
     */
    public function index()
    {
        try {
            $mascotasIds = $this->getMascotasIds();

            $suscripciones = Suscripcion::with(['user', 'mascota'])
                ->whereIn('mascota_id', $mascotasIds)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($suscripciones, 'Suscripciones obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    /**
     * Ver detalle de una suscripción
     * GET /api/entity/suscripciones/{id}
     */
    public function show(int $id)
    {
        try {
            $mascotasIds = $this->getMascotasIds();

            $suscripcion = Suscripcion::with(['user', 'mascota'])
                ->whereIn('mascota_id', $mascotasIds)
                ->findOrFail($id);

            return $this->successResponse($suscripcion, 'Suscripción obtenida');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Suscripción no encontrada', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    /**
     * Actualizar una suscripción
     * PUT /api/entity/suscripciones/{id}
     */
    public function update(Request $request, int $id)
    {
        try {
            $mascotasIds = $this->getMascotasIds();

            $suscripcion = Suscripcion::whereIn('mascota_id', $mascotasIds)->findOrFail($id);

            $validated = $request->validate([
                'monto_mensual' => 'sometimes|numeric|min:1',
                'frecuencia' => 'sometimes|in:unica,mensual,trimestral,anual',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'mensaje_apoyo' => 'nullable|string',
                'estado' => 'sometimes|in:activo,pausado,cancelado,finalizado,pendiente',
            ]);

            $suscripcion->update($validated);

            return $this->successResponse($suscripcion->load(['user', 'mascota']), 'Suscripción actualizada');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Suscripción no encontrada', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar', $e->getMessage(), 500);
        }
    }

    /**
     * Pausar suscripción
     * PATCH /api/entity/suscripciones/{id}/pausar
     */
    public function pausar(int $id)
    {
        try {
            $mascotasIds = $this->getMascotasIds();

            $suscripcion = Suscripcion::whereIn('mascota_id', $mascotasIds)
                ->where('estado', 'activo')
                ->findOrFail($id);

            $suscripcion->update(['estado' => 'pausado']);

            return $this->successResponse($suscripcion, 'Suscripción pausada');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Suscripción no encontrada o no está activa', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al pausar', $e->getMessage(), 500);
        }
    }

    /**
     * Reactivar suscripción
     * PATCH /api/entity/suscripciones/{id}/reactivar
     */
    public function reactivar(int $id)
    {
        try {
            $mascotasIds = $this->getMascotasIds();

            $suscripcion = Suscripcion::whereIn('mascota_id', $mascotasIds)
                ->where('estado', 'pausado')
                ->findOrFail($id);

            $suscripcion->update(['estado' => 'activo']);

            return $this->successResponse($suscripcion, 'Suscripción reactivada');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Suscripción no encontrada o no está pausada', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al reactivar', $e->getMessage(), 500);
        }
    }

    /**
     * Cancelar suscripción
     * PATCH /api/entity/suscripciones/{id}/cancelar
     */
    public function cancelar(int $id)
    {
        try {
            $mascotasIds = $this->getMascotasIds();

            $suscripcion = Suscripcion::whereIn('mascota_id', $mascotasIds)
                ->whereIn('estado', ['activo', 'pausado','pendiente'])
                ->findOrFail($id);

            $suscripcion->update([
                'estado' => 'cancelado',
                'fecha_fin' => now(),
            ]);

            return $this->successResponse($suscripcion, 'Suscripción cancelada');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Suscripción no encontrada', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cancelar', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas
     * GET /api/entity/suscripciones/estadisticas
     */
    public function estadisticas()
    {
        try {
            $mascotasIds = $this->getMascotasIds();

            $total = Suscripcion::whereIn('mascota_id', $mascotasIds)->count();
            $activas = Suscripcion::whereIn('mascota_id', $mascotasIds)->where('estado', 'activo')->count();
            $pausadas = Suscripcion::whereIn('mascota_id', $mascotasIds)->where('estado', 'pausado')->count();
            $canceladas = Suscripcion::whereIn('mascota_id', $mascotasIds)->where('estado', 'cancelado')->count();
            $totalMensual = Suscripcion::whereIn('mascota_id', $mascotasIds)
                ->where('estado', 'activo')
                ->sum('monto_mensual');

            return $this->successResponse([
                'total' => $total,
                'activas' => $activas,
                'pausadas' => $pausadas,
                'canceladas' => $canceladas,
                'ingreso_mensual_total' => $totalMensual,
            ], 'Estadísticas obtenidas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }
}
