<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\RegistrarMascotaRescateRequest;
use App\Services\Entity\RescateEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RescateController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected RescateEntityService $rescateService;

    public function __construct(RescateEntityService $rescateService)
    {
        $this->rescateService = $rescateService;
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

    public function completar($id)
    {
        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->completarRescate($id),
                'Error al completar rescate'
            );
            return $this->successResponse($rescate, 'Rescate completado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function disponibles(Request $request)
    {
        try {
            $rescates = $this->rescateService->getRescatesDisponibles($request);
            return $this->successResponse($rescates, 'Rescates disponibles obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function aceptar($id)
    {
        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->aceptarRescate($id),
                'Error al aceptar rescate'
            );

            return $this->successResponse($rescate, 'Rescate aceptado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function rechazar($id)
    {
        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->rechazarRescate($id),
                'Error al rechazar rescate'
            );

            return $this->successResponse($rescate, 'Rescate rechazado');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function misRescates(Request $request)
    {
        try {
            $rescates = $this->rescateService->getMisRescates();
            return $this->successResponse($rescates, 'Rescates obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function registrarMascota(RegistrarMascotaRescateRequest $request, $id)
    {
        try {
            $mascota = $this->runInTransaction(
                fn() => $this->rescateService->registrarMascotaDesdeRescate(
                    $id,
                    $request->validated(),
                    $request->only(['foto_principal'])
                ),
                'Error al registrar mascota'
            );

            return $this->successResponse($mascota, 'Mascota registrada exitosamente', 201);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar mascota', $e->getMessage(), 500);
        }
    }
}
