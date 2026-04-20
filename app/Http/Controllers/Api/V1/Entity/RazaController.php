<?php
// app/Http/Controllers/Api/V1/Entity/RazaController.php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\RazaRequest;
use App\Services\Entity\RazaEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RazaController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected RazaEntityService $razaService;

    public function __construct(RazaEntityService $razaService)
    {
        $this->razaService = $razaService;
    }

    /**
     * Listado de razas (con filtros)
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['especie', 'search']);
            $perPage = $request->get('per_page', 20);

            $razas = $this->razaService->getAll($filters, $perPage);

            return $this->successResponse($razas, 'Razas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener razas', $e->getMessage(), 500);
        }
    }

    /**
     * Mostrar detalle de raza
     */
    public function show($id)
    {
        try {
            $raza = $this->razaService->findById($id);
            return $this->successResponse($raza, 'Raza obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Raza no encontrada');
        }
    }

    /**
     * Crear nueva raza
     */
    public function store(RazaRequest $request)
    {
        try {
            $raza = $this->runInTransaction(
                fn() => $this->razaService->create($request->validated()),
                'Error al crear raza'
            );

            return $this->successResponse($raza, 'Raza creada exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la raza', $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar raza
     */
    public function update(RazaRequest $request, $id)
    {
        try {
            $raza = $this->runInTransaction(
                fn() => $this->razaService->update($id, $request->validated()),
                'Error al actualizar raza'
            );

            return $this->successResponse($raza, 'Raza actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Raza no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la raza', $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar raza (solo si no está siendo usada)
     */
    public function destroy($id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->razaService->delete($id),
                'Error al eliminar raza'
            );

            return $this->successResponse(null, 'Raza eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Raza no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    /**
     * Obtener razas por especie
     */
    public function porEspecie($especie)
    {
        try {
            $razas = $this->razaService->getPorEspecie($especie);
            return $this->successResponse($razas, 'Razas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener razas', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener todas las especies disponibles
     */
    public function especies()
    {
        try {
            $especies = $this->razaService->getEspecies();
            return $this->successResponse($especies, 'Especies obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener especies', $e->getMessage(), 500);
        }
    }
}
