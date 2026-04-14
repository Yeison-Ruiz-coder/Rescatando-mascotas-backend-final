<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeguimientoStoreRequest;
use App\Http\Requests\Admin\SeguimientoUpdateRequest;
use App\Services\SeguimientoService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SeguimientoController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected SeguimientoService $seguimientoService;

    public function __construct(SeguimientoService $seguimientoService)
    {
        $this->seguimientoService = $seguimientoService;
    }

    public function index($adopcionId)
    {
        try {
            $seguimientos = $this->seguimientoService->getByAdopcion($adopcionId, 15);
            return $this->successResponse($seguimientos, 'Seguimientos obtenidos exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        }
    }

    public function show($id)
    {
        try {
            $seguimiento = $this->seguimientoService->findById($id);
            return $this->successResponse($seguimiento, 'Seguimiento obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Seguimiento no encontrado');
        }
    }

    public function store(SeguimientoStoreRequest $request, $adopcionId)
    {
        try {
            $seguimiento = $this->runInTransaction(
                fn() => $this->seguimientoService->create(
                    $adopcionId,
                    $request->validated(),
                    $request->only(['foto_url', 'fotos_adicionales'])
                ),
                'Error al crear seguimiento'
            );

            return $this->successResponse(
                $seguimiento->load('realizadoPor'),
                'Seguimiento registrado exitosamente',
                201
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar seguimiento', $e->getMessage(), 500);
        }
    }

    public function update(SeguimientoUpdateRequest $request, $id)
    {
        try {
            $seguimiento = $this->runInTransaction(
                fn() => $this->seguimientoService->update($id, $request->validated()),
                'Error al actualizar seguimiento'
            );

            return $this->successResponse($seguimiento, 'Seguimiento actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Seguimiento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar seguimiento', $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->seguimientoService->delete($id),
                'Error al eliminar seguimiento'
            );

            return $this->successResponse(null, 'Seguimiento eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Seguimiento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar seguimiento', $e->getMessage(), 500);
        }
    }

    public function estadisticas($adopcionId)
    {
        try {
            $estadisticas = $this->seguimientoService->getEstadisticas($adopcionId);
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Adopción no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }
}
