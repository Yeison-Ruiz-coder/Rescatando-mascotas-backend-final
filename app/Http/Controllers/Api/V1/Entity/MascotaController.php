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

    /**
     * ✅ OBTENER MASCOTAS DE LA FUNDACIÓN CON FILTROS
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'buscar',
                'especie',
                'genero',
                'estado',
                'tamano',
                'per_page',
                'page',
            ]);

            $perPage = $request->get('per_page', 15);

            $mascotas = $this->mascotaService->getAllMascotas($filters, $perPage);
            return $this->successResponse($mascotas, 'Mascotas obtenidas exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en index mascotas: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), null, 403);
        }
    }

    /**
     * ✅ CREAR MASCOTA
     */
    public function store(MascotaRequest $request)
    {
        try {
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

    /**
     * ✅ ACTUALIZAR MASCOTA
     */
    public function update(MascotaRequest $request, int $id)
    {
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

    /**
     * ✅ OBTENER UNA MASCOTA
     */
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

    /**
     * ✅ ELIMINAR MASCOTA
     */
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
