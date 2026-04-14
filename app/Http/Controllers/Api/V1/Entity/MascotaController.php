<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\MascotaRequest;
use App\Services\Entity\MascotaEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MascotaController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected MascotaEntityService $mascotaService;

    public function __construct(MascotaEntityService $mascotaService)
    {
        $this->mascotaService = $mascotaService;
    }

    public function index()
    {
        try {
            $mascotas = $this->mascotaService->getAllMascotas();
            return $this->successResponse($mascotas, 'Mascotas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function store(MascotaRequest $request)
    {
        try {
            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->createMascota(
                    $request->validated(),
                    $request->only(['foto_principal', 'galeria_fotos'])
                ),
                'Error al registrar mascota'
            );

            return $this->successResponse(
                $mascota->load(['razas', 'vacunas']),
                'Mascota registrada exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar mascota', $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $mascota = $this->mascotaService->findMascota($id);
            return $this->successResponse($mascota, 'Mascota obtenida exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    public function update(MascotaRequest $request, $id)
    {
        try {
            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->updateMascota(
                    $id,
                    $request->validated(),
                    $request->only(['foto_principal'])
                ),
                'Error al actualizar mascota'
            );

            return $this->successResponse($mascota, 'Mascota actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar', $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->mascotaService->deleteMascota($id),
                'Error al eliminar mascota'
            );

            return $this->successResponse(null, 'Mascota eliminada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar', $e->getMessage(), 500);
        }
    }
}
