<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\RechazarSolicitudRequest;
use App\Services\Entity\SolicitudEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SolicitudController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected SolicitudEntityService $solicitudService;

    public function __construct(SolicitudEntityService $solicitudService)
    {
        $this->solicitudService = $solicitudService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['estado']);
            $solicitudes = $this->solicitudService->getSolicitudes($filters);
            return $this->successResponse($solicitudes, 'Solicitudes obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function show(int $id)
    {
        try {
            $solicitud = $this->solicitudService->findSolicitud($id);
            return $this->successResponse($solicitud, 'Solicitud obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function aprobar(int $id)
    {
        try {
            $resultado = $this->runInTransaction(
                fn() => $this->solicitudService->aprobarSolicitud($id),
                'Error al aprobar solicitud'
            );

            return $this->successResponse($resultado, 'Solicitud aprobada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function rechazar(RechazarSolicitudRequest $request, int $id)
    {
        try {
            $solicitud = $this->runInTransaction(
                fn() => $this->solicitudService->rechazarSolicitud($id, $request->razon_rechazo),
                'Error al rechazar solicitud'
            );

            return $this->successResponse($solicitud, 'Solicitud rechazada');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Solicitud no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function estadisticas()
    {
        try {
            $estadisticas = $this->solicitudService->getEstadisticas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }
}
