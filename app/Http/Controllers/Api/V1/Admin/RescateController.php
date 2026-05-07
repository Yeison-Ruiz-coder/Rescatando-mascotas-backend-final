<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RescateRequest;
use App\Http\Requests\Admin\AsignarRescateRequest;
use App\Services\RescateService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RescateController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected RescateService $rescateService;

    public function __construct(RescateService $rescateService)
    {
        $this->rescateService = $rescateService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['estado', 'tipo_emergencia', 'prioridad']);
        $perPage = $request->get('per_page', 20);

        $rescates = $this->rescateService->getAll($filters, $perPage);

        return $this->successResponse($rescates, 'Rescates obtenidos exitosamente');
    }

    public function store(RescateRequest $request)
    {
        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->create(
                    $request->validated(),
                    $request->file('foto_principal'),
                    $request->file('galeria_fotos', [])
                ),
                'Error al crear rescate'
            );

            return $this->successResponse(
                $rescate->load(['usuarioReporto', 'entidadResponsable']),
                'Rescate creado exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el rescate', $e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $rescate = $this->rescateService->findById($id);
            return $this->successResponse($rescate, 'Rescate obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        }
    }

    public function update(RescateRequest $request, int $id)
    {
        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->update($id, $request->validated(), $request->file('foto_principal')),
                'Error al actualizar rescate'
            );

            return $this->successResponse($rescate, 'Rescate actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el rescate', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->rescateService->delete($id),
                'Error al eliminar rescate'
            );

            return $this->successResponse(null, 'Rescate eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el rescate', $e->getMessage(), 500);
        }
    }

    public function asignar(AsignarRescateRequest $request, int $id)
    {
        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->asignar(
                    $id,
                    $request->entidad_tipo,
                    $request->entidad_id
                ),
                'Error al asignar rescate'
            );

            return $this->successResponse($rescate, 'Rescate asignado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate o entidad no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al asignar rescate', $e->getMessage(), 500);
        }
    }

    public function actualizarEstado(Request $request, int $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,completado,seguimiento'
        ]);

        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->updateEstado($id, $request->estado),
                'Error al actualizar estado'
            );

            return $this->successResponse($rescate, 'Estado actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el estado', $e->getMessage(), 500);
        }
    }

    public function estadisticas()
    {
        try {
            $estadisticas = $this->rescateService->getEstadisticas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }

    public function agregarFotos(Request $request, int $id)
    {
        $request->validate([
            'fotos' => 'required|array',
            'fotos.*' => 'image|max:5120'
        ]);

        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->agregarFotos($id, $request->file('fotos')),
                'Error al agregar fotos'
            );

            return $this->successResponse($rescate, 'Fotos agregadas exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al agregar fotos', $e->getMessage(), 500);
        }
    }
}
