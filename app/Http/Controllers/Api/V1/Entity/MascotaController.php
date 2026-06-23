<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\MascotaRequest;
use App\Services\Entity\MascotaEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class MascotaController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected MascotaEntityService $mascotaService;

    public function __construct(MascotaEntityService $mascotaService)
    {
        $this->mascotaService = $mascotaService;
    }

    public function actualizarEstado(Request $request, int $id)
    {
        try {
            // ✅ Validación solo del campo estado
            $request->validate([
                'estado' => 'required|in:Adoptado,En adopcion,Rescatada,En acogida'
            ]);

            // ✅ Obtener la mascota (ya valida que pertenezca a la fundación)
            $mascota = $this->mascotaService->findMascota($id);

            // ✅ Actualizar SOLO el estado
            $mascota->estado = $request->estado;
            $mascota->save();

            return $this->successResponse(
                $mascota->load(['razas', 'vacunas']),
                'Estado actualizado exitosamente'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        } catch (\Exception $e) {
            Log::error('Error en actualizarEstado: ' . $e->getMessage());
            return $this->errorResponse('Error al actualizar estado', $e->getMessage(), 500);
        }
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
            // Preparar los archivos para enviar al servicio
            $files = [];

            if ($request->hasFile('foto_principal')) {
                $files['foto_principal'] = $request->file('foto_principal');
            }

            if ($request->hasFile('galeria_fotos')) {
                $files['galeria_fotos'] = $request->file('galeria_fotos');
            }

            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->createMascota(
                    $request->validated(),
                    $files
                ),
                'Error al registrar mascota'
            );

            return $this->successResponse(
                $mascota->load(['razas', 'vacunas']),
                'Mascota registrada exitosamente',
                201
            );
        } catch (\Exception $e) {
            Log::error('Error en store mascota: ' . $e->getMessage());
            return $this->errorResponse('Error al registrar mascota', $e->getMessage(), 500);
        }
    }


    public function update(MascotaRequest $request, int $id)
    {
        // ✅ LOG PARA VER QUÉ LLEGA
        Log::info('📝 UPDATE MASCOTA - DATOS:', $request->all());
        Log::info('📝 UPDATE MASCOTA - FILES:', array_keys($request->allFiles()));

        try {
            $files = [];

            if ($request->hasFile('foto_principal')) {
                $files['foto_principal'] = $request->file('foto_principal');
            }

            if ($request->hasFile('galeria_fotos')) {
                $files['galeria_fotos'] = $request->file('galeria_fotos');
                Log::info('📸 Galería recibida:', ['count' => count($files['galeria_fotos'])]);
            }

            // ✅ PROCESAR fotos_eliminar (viene en el request, no en validated)
            $validatedData = $request->validated();

            if ($request->has('fotos_eliminar')) {
                $validatedData['fotos_eliminar'] = $request->input('fotos_eliminar');
                Log::info('🗑️ Fotos a eliminar:', $validatedData['fotos_eliminar']);
            }

            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->updateMascota(
                    $id,
                    $validatedData,
                    $files
                ),
                'Error al actualizar mascota'
            );

            return $this->successResponse($mascota, 'Mascota actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        } catch (\Exception $e) {
            Log::error('❌ Error en update mascota:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->errorResponse('Error al actualizar', $e->getMessage(), 500);
        }
    }

    public function show(int $id)
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


    public function destroy(int $id)
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
