<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SolicitudRequest;
use App\Http\Requests\Admin\CambiarEstadoSolicitudRequest;
use App\Services\SolicitudService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SolicitudController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected SolicitudService $solicitudService;

    public function __construct(SolicitudService $solicitudService)
    {
        $this->solicitudService = $solicitudService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['estado', 'tipo_solicitud', 'buscar']);
        $perPage = $request->get('per_page', 15);

        $solicitudes = $this->solicitudService->getAll($filters, $perPage);
        $estadisticas = $this->solicitudService->getEstadisticas();

        return $this->successResponse([
            'data' => $solicitudes,
            'estadisticas' => $estadisticas
        ], 'Solicitudes obtenidas exitosamente');
    }

    public function show(int $id)
    {
        try {
            $solicitud = $this->solicitudService->findById($id);
            return $this->successResponse($solicitud, 'Solicitud obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        }
    }

    public function store(SolicitudRequest $request)
    {
        try {
            $solicitud = $this->runInTransaction(
                fn() => $this->solicitudService->create($request->validated()),
                'Error al crear solicitud'
            );

            return $this->successResponse(
                $solicitud->load(['usuario', 'solicitable']),
                'Solicitud creada exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la solicitud', $e->getMessage(), 500);
        }
    }

    public function update(SolicitudRequest $request, int $id)
    {
        try {
            $solicitud = $this->runInTransaction(
                fn() => $this->solicitudService->update($id, $request->only([
                    'tipo_solicitud', 'contenido', 'estado', 'notas_internas', 'razon_rechazo'
                ])),
                'Error al actualizar solicitud'
            );

            return $this->successResponse($solicitud, 'Solicitud actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la solicitud', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->solicitudService->delete($id),
                'Error al eliminar solicitud'
            );

            return $this->successResponse(null, 'Solicitud eliminada correctamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la solicitud', $e->getMessage(), 500);
        }
    }

    public function cambiarEstado(CambiarEstadoSolicitudRequest $request, int $id)
    {
        try {
            $solicitud = $this->runInTransaction(
                fn() => $this->solicitudService->cambiarEstado(
                    $id,
                    $request->estado,
                    $request->razon_rechazo
                ),
                'Error al cambiar estado'
            );

            return $this->successResponse(
                $solicitud->fresh(['usuario', 'revisor', 'solicitable']),
                'Estado actualizado correctamente'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el estado', $e->getMessage(), 500);
        }
    }

    public function estadisticas()
    {
        try {
            $estadisticas = $this->solicitudService->getEstadisticasAvanzadas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }
}
