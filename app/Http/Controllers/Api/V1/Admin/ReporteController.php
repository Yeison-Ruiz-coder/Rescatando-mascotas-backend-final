<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReporteRequest;
use App\Http\Requests\Admin\ReporteUpdateRequest;
use App\Http\Requests\Admin\ConvertirRescateRequest;
use App\Http\Requests\Admin\ReportesCercanosRequest;
use App\Services\ReporteService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReporteController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected ReporteService $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['tipo_reporte', 'estado', 'urgencia']);

        // Filtro de cercanía
        if ($request->has(['lat', 'lng'])) {
            $filters['cercanos'] = true;
            $filters['lat'] = $request->lat;
            $filters['lng'] = $request->lng;
            $filters['radio'] = $request->get('radio', 10);
        }

        $perPage = $request->get('per_page', 20);

        $reportes = $this->reporteService->getAll($filters, $perPage);
        $estadisticas = $this->reporteService->getEstadisticas();

        return $this->successResponse([
            'data' => $reportes,
            'estadisticas' => $estadisticas
        ], 'Reportes obtenidos exitosamente');
    }

    public function show(int $id)
    {
        try {
            $reporte = $this->reporteService->findById($id);
            return $this->successResponse($reporte, 'Reporte obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Reporte no encontrado');
        }
    }

    public function store(ReporteRequest $request)
    {
        try {
            $reporte = $this->runInTransaction(
                fn() => $this->reporteService->create(
                    $request->validated(),
                    $request->file('foto_url')
                ),
                'Error al crear reporte'
            );

            return $this->successResponse(
                $reporte->load('usuario'),
                'Reporte creado exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el reporte', $e->getMessage(), 500);
        }
    }

    public function update(ReporteUpdateRequest $request, int $id)
    {
        try {
            $reporte = $this->runInTransaction(
                fn() => $this->reporteService->update($id, $request->validated()),
                'Error al actualizar reporte'
            );

            return $this->successResponse($reporte, 'Reporte actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Reporte no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el reporte', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->reporteService->delete($id),
                'Error al eliminar reporte'
            );

            return $this->successResponse(null, 'Reporte eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Reporte no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el reporte', $e->getMessage(), 500);
        }
    }

    public function convertirARescate(ConvertirRescateRequest $request, int $id)
    {
        try {
            $resultado = $this->runInTransaction(
                fn() => [
                    'reporte' => $this->reporteService->findById($id),
                    'rescate' => $this->reporteService->convertirARescate($id, $request->validated())
                ],
                'Error al convertir reporte'
            );

            return $this->successResponse([
                'reporte' => $resultado['reporte']->fresh(),
                'rescate' => $resultado['rescate']->load('usuarioReporto')
            ], 'Rescate creado a partir del reporte exitosamente', 201);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Reporte no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear rescate', $e->getMessage(), 500);
        }
    }

    public function estadisticas()
    {
        try {
            $estadisticas = $this->reporteService->getEstadisticasAvanzadas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }

    public function cercanos(ReportesCercanosRequest $request)
    {
        try {
            $reportes = $this->reporteService->getCercanos(
                $request->latitud,
                $request->longitud,
                $request->get('radio', 10)
            );

            return $this->successResponse($reportes, 'Reportes cercanos obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener reportes cercanos', $e->getMessage(), 500);
        }
    }
}
