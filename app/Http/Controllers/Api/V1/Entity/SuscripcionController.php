<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Services\Entity\SuscripcionEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class SuscripcionController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected SuscripcionEntityService $suscripcionService;

    public function __construct(SuscripcionEntityService $suscripcionService)
    {
        $this->suscripcionService = $suscripcionService;
    }

    public function index()
    {
        try {
            $suscripciones = $this->suscripcionService->getMisSuscripciones();
            return $this->successResponse($suscripciones, 'Suscripciones obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:1',
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'required|in:activo,pausado,cancelado,finalizado'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $suscripcion = $this->runInTransaction(
                fn() => $this->suscripcionService->createSuscripcion($request->all()),
                'Error al crear suscripción'
            );

            return $this->successResponse($suscripcion, 'Suscripción creada exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la suscripción', $e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $suscripcion = $this->suscripcionService->findSuscripcion($id);
            return $this->successResponse($suscripcion, 'Suscripción obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'mascota_id' => 'sometimes|exists:mascotas,id',
            'monto_mensual' => 'sometimes|numeric|min:1',
            'frecuencia' => 'sometimes|in:unica,mensual,trimestral,anual',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'sometimes|in:activo,pausado,cancelado,finalizado'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $suscripcion = $this->runInTransaction(
                fn() => $this->suscripcionService->updateSuscripcion($id, $request->all()),
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
                fn() => $this->suscripcionService->deleteSuscripcion($id),
                'Error al eliminar suscripción'
            );

            return $this->successResponse(null, 'Suscripción eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Suscripción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la suscripción', $e->getMessage(), 500);
        }
    }

    public function porMascota(int $mascotaId)
    {
        try {
            $suscripciones = $this->suscripcionService->getSuscripcionesPorMascota($mascotaId);
            return $this->successResponse($suscripciones, 'Suscripciones obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function estadisticas()
    {
        try {
            $estadisticas = $this->suscripcionService->getEstadisticas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }
}
