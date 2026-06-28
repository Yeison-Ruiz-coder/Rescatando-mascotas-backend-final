<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Services\SuscripcionService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
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
}
