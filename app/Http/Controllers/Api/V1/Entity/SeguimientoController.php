<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\SeguimientoRequest;
use App\Services\Entity\SeguimientoEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class SeguimientoController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected SeguimientoEntityService $seguimientoService;

    public function __construct(SeguimientoEntityService $seguimientoService)
    {
        $this->seguimientoService = $seguimientoService;
    }

    /**
     * ✅ LISTAR SEGUIMIENTOS DE UNA ADOPCIÓN
     */
    public function index(Request $request, int $adopcionId)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $seguimientos = $this->seguimientoService->getSeguimientosPorAdopcion($adopcionId, $perPage);

            return $this->successResponse($seguimientos, 'Seguimientos obtenidos exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en index seguimientos: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    /**
     * ✅ CREAR NUEVO SEGUIMIENTO
     */
    public function store(SeguimientoRequest $request, int $adopcionId)
    {
        try {
            $seguimiento = $this->runInTransaction(
                fn() => $this->seguimientoService->createSeguimiento(
                    $adopcionId,
                    $request->validated(),
                    $request->file('fotos_adicionales', [])
                ),
                'Error al crear seguimiento'
            );

            return $this->successResponse($seguimiento, 'Seguimiento creado exitosamente', 201);
        } catch (\Exception $e) {
            Log::error('Error en store seguimiento: ' . $e->getMessage());
            return $this->errorResponse('Error al crear seguimiento', $e->getMessage(), 500);
        }
    }

    /**
     * ✅ OBTENER UN SEGUIMIENTO
     */
    public function show(int $id)
    {
        try {
            $seguimiento = $this->seguimientoService->findSeguimiento($id);
            return $this->successResponse($seguimiento, 'Seguimiento obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Seguimiento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    /**
     * ✅ ACTUALIZAR SEGUIMIENTO
     */
    public function update(SeguimientoRequest $request, int $id)
    {
        try {
            $seguimiento = $this->runInTransaction(
                fn() => $this->seguimientoService->updateSeguimiento(
                    $id,
                    $request->validated(),
                    $request->file('fotos_adicionales', [])
                ),
                'Error al actualizar seguimiento'
            );

            return $this->successResponse($seguimiento, 'Seguimiento actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Seguimiento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar seguimiento', $e->getMessage(), 500);
        }
    }

    /**
     * ✅ ELIMINAR SEGUIMIENTO
     */
    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->seguimientoService->deleteSeguimiento($id),
                'Error al eliminar seguimiento'
            );

            return $this->successResponse(null, 'Seguimiento eliminado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Seguimiento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar seguimiento', $e->getMessage(), 500);
        }
    }

    /**
     * ✅ OBTENER PRÓXIMOS SEGUIMIENTOS PENDIENTES
     */
    public function pendientes(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $pendientes = $this->seguimientoService->getSeguimientosPendientes($perPage);

            return $this->successResponse($pendientes, 'Seguimientos pendientes obtenidos exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en pendientes seguimientos: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    /**
     * ✅ OBTENER ESTADÍSTICAS DE SEGUIMIENTOS
     */
    public function estadisticas(Request $request)
    {
        try {
            $estadisticas = $this->seguimientoService->getEstadisticas();
            return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en estadisticas seguimientos: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    /**
     * ✅ MARCAR SEGUIMIENTO COMO COMPLETADO
     */
    public function completar(int $id)
    {
        try {
            $seguimiento = $this->runInTransaction(
                fn() => $this->seguimientoService->completarSeguimiento($id),
                'Error al completar seguimiento'
            );

            return $this->successResponse($seguimiento, 'Seguimiento completado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Seguimiento no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al completar seguimiento', $e->getMessage(), 500);
        }
    }
}
