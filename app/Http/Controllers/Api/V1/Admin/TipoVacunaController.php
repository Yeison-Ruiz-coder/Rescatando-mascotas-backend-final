<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TipoVacunaRequest;
use App\Services\TipoVacunaService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TipoVacunaController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected TipoVacunaService $tipoVacunaService;

    public function __construct(TipoVacunaService $tipoVacunaService)
    {
        $this->tipoVacunaService = $tipoVacunaService;
    }

    /**
     * Listado de tipos de vacuna
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $perPage = $request->get('per_page', 20);

        $tiposVacunas = $this->tipoVacunaService->getAll($filters, $perPage);
        $estadisticas = $this->tipoVacunaService->getEstadisticas();

        return $this->successResponse([
            'data' => $tiposVacunas,
            'estadisticas' => $estadisticas
        ], 'Tipos de vacuna obtenidos exitosamente');
    }

    /**
     * Mostrar detalle de tipo de vacuna
     */
    public function show($id)
    {
        try {
            $tipoVacuna = $this->tipoVacunaService->findById($id);
            $estadisticas = $this->tipoVacunaService->getDetalleEstadisticas($tipoVacuna);

            return $this->successResponse([
                'tipo_vacuna' => $tipoVacuna,
                'estadisticas' => $estadisticas
            ], 'Tipo de vacuna obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Tipo de vacuna no encontrado');
        }
    }

    /**
     * Crear nuevo tipo de vacuna
     */
    public function store(TipoVacunaRequest $request)
    {
        try {
            $tipoVacuna = $this->runInTransaction(
                fn() => $this->tipoVacunaService->create($request->validated()),
                'Error al crear tipo de vacuna'
            );

            return $this->successResponse($tipoVacuna, 'Tipo de vacuna creado exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el tipo de vacuna', $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar tipo de vacuna
     */
    public function update(TipoVacunaRequest $request, $id)
    {
        try {
            $tipoVacuna = $this->runInTransaction(
                fn() => $this->tipoVacunaService->update($id, $request->validated()),
                'Error al actualizar tipo de vacuna'
            );

            return $this->successResponse($tipoVacuna, 'Tipo de vacuna actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Tipo de vacuna no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el tipo de vacuna', $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar tipo de vacuna
     */
    public function destroy($id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->tipoVacunaService->delete($id),
                'Error al eliminar tipo de vacuna'
            );

            return $this->successResponse(null, 'Tipo de vacuna eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Tipo de vacuna no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    /**
     * Vacunas recomendadas por especie
     */
    public function recomendadas(Request $request)
    {
        $especie = $request->get('especie');

        try {
            $vacunasRecomendadas = $this->tipoVacunaService->getRecomendadas($especie);

            $data = $especie
                ? [$especie => $vacunasRecomendadas]
                : $vacunasRecomendadas;

            return $this->successResponse($data, 'Vacunas recomendadas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener vacunas recomendadas', $e->getMessage(), 500);
        }
    }

    /**
     * Estadísticas de vacunación
     */
    public function estadisticas()
    {
        try {
            $estadisticas = $this->tipoVacunaService->getEstadisticasVacunacion();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e->getMessage(), 500);
        }
    }
}
