<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\MascotaRequest;
use App\Services\Entity\MascotaEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Support\Facades\Log;
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

    // app/Http/Controllers/Api/V1/Entity/MascotaController.php

    public function update(MascotaRequest $request, int $id)
    {
        Log::info('=== CONTROLLER UPDATE ===');
        Log::info('Has galeria_fotos?', ['has' => $request->hasFile('galeria_fotos')]);
        Log::info('All files keys:', array_keys($request->allFiles()));
        Log::info('Fotos eliminar:', $request->input('fotos_eliminar', []));
        try {
            $files = [];

            // ✅ Capturar foto principal
            if ($request->hasFile('foto_principal')) {
                $files['foto_principal'] = $request->file('foto_principal');
            }

            // ✅ ✅ ✅ CAPTURAR GALERÍA DE FOTOS (esto estaba faltando)
            if ($request->hasFile('galeria_fotos')) {
                $files['galeria_fotos'] = $request->file('galeria_fotos');
                Log::info('📸 Galería de fotos recibida en controller:', [
                    'count' => count($request->file('galeria_fotos'))
                ]);
            }

            // ✅ Capturar fotos a eliminar (vienen en el request validated)
            $validatedData = $request->validated();

            // Asegurar que fotos_eliminar se pase al servicio
            if ($request->has('fotos_eliminar')) {
                $validatedData['fotos_eliminar'] = $request->input('fotos_eliminar');
                Log::info('🗑️ Fotos a eliminar:', $validatedData['fotos_eliminar']);
            }

            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->updateMascota(
                    $id,
                    $validatedData,  // Pasar los datos validados CON fotos_eliminar
                    $files
                ),
                'Error al actualizar mascota'
            );

            return $this->successResponse($mascota, 'Mascota actualizada exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        } catch (\Exception $e) {
            Log::error('Error en update mascota: ' . $e->getMessage());
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
