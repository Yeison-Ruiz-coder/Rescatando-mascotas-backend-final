<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FundacionRequest;
use App\Http\Requests\Admin\NecesidadesRequest;
use App\Services\FundacionService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FundacionController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected FundacionService $fundacionService;

    public function __construct(FundacionService $fundacionService)
    {
        $this->fundacionService = $fundacionService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'recibe_voluntarios']);
        $perPage = $request->get('per_page', 15);

        $fundaciones = $this->fundacionService->getAll($filters, $perPage);
        $estadisticas = $this->fundacionService->getEstadisticas();

        return $this->successResponse([
            'data' => $fundaciones,
            'estadisticas' => $estadisticas
        ], 'Fundaciones obtenidas exitosamente');
    }

    public function show($id)
    {
        try {
            $fundacion = $this->fundacionService->findById($id);
            $estadisticas = $this->fundacionService->getDetalleEstadisticas($fundacion);
            $necesidades = json_decode($fundacion->necesidades_actuales, true) ?? [];

            return $this->successResponse([
                'fundacion' => $fundacion,
                'estadisticas' => $estadisticas,
                'necesidades' => $necesidades,
            ], 'Fundación obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Fundación no encontrada');
        }
    }

    public function store(FundacionRequest $request)
    {
        try {
            $fundacion = $this->runInTransaction(
                fn() => $this->fundacionService->create($request->validated()),
                'Error al crear fundación'
            );

            return $this->successResponse($fundacion->load('usuarioPrincipal'), 'Fundación creada exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la fundación', $e->getMessage(), 500);
        }
    }

    public function update(FundacionRequest $request, $id)
    {
        try {
            $fundacion = $this->runInTransaction(
                fn() => $this->fundacionService->update($id, $request->validated()),
                'Error al actualizar fundación'
            );

            return $this->successResponse($fundacion->load('usuarioPrincipal'), 'Fundación actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Fundación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la fundación', $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->fundacionService->delete($id),
                'Error al eliminar fundación'
            );

            return $this->successResponse(null, 'Fundación eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Fundación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function necesidades($id)
    {
        try {
            $necesidades = $this->fundacionService->getNecesidades($id);
            return $this->successResponse($necesidades, 'Necesidades obtenidas exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Fundación no encontrada');
        }
    }

    public function actualizarNecesidades(NecesidadesRequest $request, $id)
    {
        try {
            $fundacion = $this->runInTransaction(
                fn() => $this->fundacionService->actualizarNecesidades($id, $request->necesidades),
                'Error al actualizar necesidades'
            );

            return $this->successResponse($request->necesidades, 'Necesidades actualizadas exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Fundación no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar necesidades', $e->getMessage(), 500);
        }
    }
}
