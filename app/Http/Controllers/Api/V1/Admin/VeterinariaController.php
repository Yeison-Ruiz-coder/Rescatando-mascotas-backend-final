<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VeterinariaRequest;
use App\Http\Requests\Admin\VeterinariasCercanasRequest;
use App\Services\VeterinariaService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VeterinariaController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected VeterinariaService $veterinariaService;

    public function __construct(VeterinariaService $veterinariaService)
    {
        $this->veterinariaService = $veterinariaService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'urgencias_24h', 'verificado', 'ciudad', 'servicio']);
        $perPage = $request->get('per_page', 15);

        $veterinarias = $this->veterinariaService->getAll($filters, $perPage);
        $estadisticas = $this->veterinariaService->getEstadisticas();

        return $this->successResponse([
            'data' => $veterinarias,
            'estadisticas' => $estadisticas
        ], 'Veterinarias obtenidas exitosamente');
    }

    public function show(int $id)
    {
        try {
            $veterinaria = $this->veterinariaService->findById($id);
            $estadisticas = $this->veterinariaService->getDetalleEstadisticas($veterinaria);

            $servicios = [];
            if ($veterinaria->servicios) {
                $servicios = is_string($veterinaria->servicios)
                    ? json_decode($veterinaria->servicios, true)
                    : ($veterinaria->servicios ?? []);
            }

            $convenios = [];
            if ($veterinaria->convenios) {
                $convenios = is_string($veterinaria->convenios)
                    ? json_decode($veterinaria->convenios, true)
                    : ($veterinaria->convenios ?? []);
            }

            $serviciosDetallados = [];
            if ($veterinaria->servicios_detallados) {
                $serviciosDetallados = is_string($veterinaria->servicios_detallados)
                    ? json_decode($veterinaria->servicios_detallados, true)
                    : ($veterinaria->servicios_detallados ?? []);
            }

            return $this->successResponse([
                'veterinaria' => $veterinaria,
                'servicios' => $servicios,
                'servicios_detallados' => $serviciosDetallados,
                'convenios' => $convenios,
                'estadisticas' => $estadisticas,
            ], 'Veterinaria obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Veterinaria no encontrada');
        }
    }

    public function store(VeterinariaRequest $request)
    {
        try {
            $veterinaria = $this->runInTransaction(
                fn() => $this->veterinariaService->create($request->validated()),
                'Error al crear veterinaria'
            );

            return $this->successResponse($veterinaria, 'Veterinaria creada exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la veterinaria', $e->getMessage(), 500);
        }
    }

    public function update(VeterinariaRequest $request,int $id)
    {
        try {
            $veterinaria = $this->runInTransaction(
                fn() => $this->veterinariaService->update($id, $request->validated()),
                'Error al actualizar veterinaria'
            );

            return $this->successResponse($veterinaria, 'Veterinaria actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Veterinaria no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la veterinaria', $e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->veterinariaService->delete($id),
                'Error al eliminar veterinaria'
            );

            return $this->successResponse(null, 'Veterinaria eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Veterinaria no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function cercanas(VeterinariasCercanasRequest $request)
    {
        try {
            $veterinarias = $this->veterinariaService->getCercanas(
                $request->latitud,
                $request->longitud,
                $request->get('radio', 10)
            );

            return $this->successResponse($veterinarias, 'Veterinarias cercanas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener veterinarias cercanas', $e->getMessage(), 500);
        }
    }
}
