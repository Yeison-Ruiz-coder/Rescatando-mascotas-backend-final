<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DonacionRequest;
use App\Http\Requests\Admin\DonacionReporteRequest;
use App\Services\DonacionService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DonacionController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected DonacionService $donacionService;

    public function __construct(DonacionService $donacionService)
    {
        $this->donacionService = $donacionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['user_id', 'fundacion_id', 'publica', 'fecha_inicio', 'fecha_fin', 'orden', 'metodo_pago']);
        $perPage = $request->get('per_page', 15);

        $donaciones = $this->donacionService->getAll($filters, $perPage);
        $totales = $this->donacionService->getTotales();

        return $this->successResponse([
            'data' => $donaciones,
            'totales' => $totales
        ], 'Donaciones obtenidas exitosamente');
    }

    public function show(int $id)
    {
        try {
            $donacion = $this->donacionService->findById($id);
            return $this->successResponse($donacion, 'Donación obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Donación no encontrada');
        }
    }

    public function store(DonacionRequest $request)
    {
        try {
            $donacion = $this->runInTransaction(
                fn() => $this->donacionService->create($request->validated()),
                'Error al crear donación'
            );

            return $this->successResponse(
                $donacion->load(['user', 'fundacion']),
                'Donación registrada exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar la donación', $e->getMessage(), 500);
        }
    }

    public function update(DonacionRequest $request, int $id)
    {
        try {
            $donacion = $this->runInTransaction(
                fn() => $this->donacionService->update($id, $request->validated()),
                'Error al actualizar donación'
            );

            return $this->successResponse($donacion, 'Donación actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Donación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la donación', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->donacionService->delete($id),
                'Error al eliminar donación'
            );

            return $this->successResponse(null, 'Donación eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Donación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la donación', $e->getMessage(), 500);
        }
    }

    public function togglePublica(int $id)
    {
        try {
            $donacion = $this->runInTransaction(
                fn() => $this->donacionService->togglePublica($id),
                'Error al cambiar visibilidad'
            );

            return $this->successResponse(
                ['publica' => $donacion->publica],
                'Visibilidad actualizada correctamente'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Donación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cambiar visibilidad', $e->getMessage(), 500);
        }
    }

    public function reporte(DonacionReporteRequest $request)
    {
        try {
            $reporte = $this->donacionService->getReporte(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->fundacion_id,
                $request->agrupacion
            );

            return $this->successResponse($reporte, 'Reporte generado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar el reporte', $e->getMessage(), 500);
        }
    }

    public function porMetodoPago(Request $request)
    {
        try {
            $resultado = $this->donacionService->getPorMetodoPago(
                $request->get('fecha_inicio', now()->startOfMonth()),
                $request->get('fecha_fin', now())
            );

            return $this->successResponse($resultado, 'Donaciones por método de pago obtenidas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener donaciones por método de pago', $e->getMessage(), 500);
        }
    }
}
