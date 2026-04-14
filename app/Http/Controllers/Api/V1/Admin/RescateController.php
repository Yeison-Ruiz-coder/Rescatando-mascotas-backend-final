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

    public function show($id)
    {
        try {
            $rescate = $this->rescateService->findById($id);
            return $this->successResponse($rescate, 'Rescate obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        }
    }

    public function asignar(AsignarRescateRequest $request, $id)
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

    public function estadisticas()
    {
        try {
            $estadisticas = $this->rescateService->getEstadisticas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }
}
