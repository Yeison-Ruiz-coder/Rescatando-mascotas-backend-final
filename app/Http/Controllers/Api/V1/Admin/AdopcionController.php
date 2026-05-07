<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdopcionRequest;
use App\Http\Requests\Admin\CambiarEstadoAdopcionRequest;
use App\Http\Requests\Admin\SeguimientoRequest;
use App\Services\AdopcionService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdopcionController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected AdopcionService $adopcionService;

    public function __construct(AdopcionService $adopcionService)
    {
        $this->adopcionService = $adopcionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['estado', 'fundacion_id', 'user_id']);
        $perPage = $request->get('per_page', 15);

        $adopciones = $this->adopcionService->getAll($filters, $perPage);

        return $this->successResponse($adopciones, 'Adopciones obtenidas exitosamente');
    }

    public function show(int $id)
    {
        try {
            $adopcion = $this->adopcionService->findById($id);
            return $this->successResponse($adopcion, 'Adopción obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        }
    }

    public function store(AdopcionRequest $request)
    {
        try {
            $adopcion = $this->runInTransaction(
                fn() => $this->adopcionService->create($request->validated()),
                'Error al crear adopción'
            );

            return $this->successResponse(
                $adopcion->load(['adoptante', 'mascota']),
                'Adopción creada',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear adopción', $e->getMessage(), 500);
        }
    }

    public function update(AdopcionRequest $request, int $id)
    {
        try {
            $adopcion = $this->runInTransaction(
                fn() => $this->adopcionService->update($id, $request->validated()),
                'Error al actualizar adopción'
            );

            return $this->successResponse($adopcion, 'Adopción actualizada');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->adopcionService->delete($id),
                'Error al eliminar adopción'
            );

            return $this->successResponse(null, 'Adopción eliminada');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function cambiarEstado(CambiarEstadoAdopcionRequest $request, int $id)
    {
        try {
            $adopcion = $this->runInTransaction(
                fn() => $this->adopcionService->cambiarEstado($id, $request->estado, $request->razon_rechazo),
                'Error al cambiar estado'
            );

            return $this->successResponse($adopcion, 'Estado actualizado');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar estado', $e->getMessage(), 500);
        }
    }

    public function seguimientos(int $id)
    {
        try {
            $seguimientos = $this->adopcionService->getSeguimientos($id);
            return $this->successResponse($seguimientos, 'Seguimientos obtenidos');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        }
    }

    public function storeSeguimiento(SeguimientoRequest $request, int $id)
    {
        try {
            $seguimiento = $this->runInTransaction(
                fn() => $this->adopcionService->crearSeguimiento($id, $request->validated()),
                'Error al crear seguimiento'
            );

            return $this->successResponse($seguimiento, 'Seguimiento registrado', 201);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar seguimiento', $e->getMessage(), 500);
        }
    }
}
