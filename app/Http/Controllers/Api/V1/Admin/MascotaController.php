<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MascotaRequest;
use App\Models\Mascota;
use Illuminate\Http\Request;
use App\Services\MascotaService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;

class MascotaController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected $mascotaService;

    public function __construct(MascotaService $mascotaService)
    {
        $this->mascotaService = $mascotaService;
    }

    public function index(Request $request)
    {
        $mascotas = Mascota::with(['fundacion', 'razas', 'vacunas'])
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->especie, fn($q) => $q->where('especie', $request->especie))
            ->when($request->fundacion_id, fn($q) => $q->where('fundacion_id', $request->fundacion_id))
            ->when($request->buscar, fn($q) => $q->where('nombre_mascota', 'like', "%{$request->buscar}%"))
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($mascotas);
    }

    public function store(MascotaRequest $request)
    {
        try {
            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->create($request->validated(), $request->file()),
                'Error al crear mascota'
            );

            return $this->successResponse(
                $mascota->load(['fundacion', 'razas', 'vacunas']),
                'Mascota creada exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear mascota', $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $mascota = Mascota::with(['fundacion', 'razas', 'vacunas', 'historialMedico', 'adopciones'])
                ->findOrFail($id);
            return $this->successResponse($mascota);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        }
    }

    public function update(MascotaRequest $request, $id)
    {
        try {
            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->update($id, $request->validated(), $request->file()),
                'Error al actualizar mascota'
            );

            return $this->successResponse(
                $mascota->fresh(['fundacion', 'razas', 'vacunas']),
                'Mascota actualizada exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar mascota', $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->runInTransaction(
                fn() => $this->mascotaService->delete($id),
                'Error al eliminar mascota'
            );
            return $this->successResponse(null, 'Mascota eliminada exitosamente', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar mascota', $e->getMessage(), 500);
        }
    }
}
