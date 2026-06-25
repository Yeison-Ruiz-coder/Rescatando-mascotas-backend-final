<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\MascotaRequest;
use App\Services\Entity\MascotaEntityService;
use App\Traits\ApiResponses;
use App\Models\Fundacion;
use App\Traits\TransactionTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            // 🔥 VERIFICAR QUE EL USUARIO ESTÁ AUTENTICADO
            $user = auth()->user();

            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', null, 401);
            }

            // 🔥 VERIFICAR QUE EL USUARIO SEA FUNDACIÓN
            if ($user->tipo !== 'fundacion') {
                return $this->errorResponse('El usuario debe ser de tipo fundación', null, 403);
            }

            // 🔥 VERIFICAR QUE TENGA FUNDACIÓN ASOCIADA
            $fundacion = $user->fundacion;

            // Si no tiene fundación, crearla automáticamente
            if (!$fundacion) {
                $fundacion = Fundacion::create([
                    'Nombre_1' => $user->nombre ?? 'Fundación de ' . $user->email,
                    'user_id' => $user->id,
                    'Email' => $user->email,
                    'Direccion' => $user->direccion ?? 'Por definir',
                    'Telefono' => $user->telefono ?? '000000000',
                    'registro_sanitario' => 'PENDIENTE_' . $user->id,
                    'ciudad' => $user->ciudad ?? null,
                    'recibe_voluntarios' => false,
                    'capacidad_maxima' => 0,
                ]);
                Log::info('✅ Fundación creada automáticamente para usuario: ' . $user->id);
            }

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

            // Pasar el ID de la fundación al service
            $mascotas = $this->mascotaService->getAllMascotas($fundacion->id, $filters, $perPage);
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

    /**
     * ✅ ACTUALIZAR ESTADO DE MASCOTA (PATCH)
     */
    public function actualizarEstado(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:En adopcion,Adoptado,Rescatada,En acogida'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $mascota = $this->runInTransaction(
                fn() => $this->mascotaService->actualizarEstado($id, $request->estado),
                'Error al actualizar estado'
            );

            return $this->successResponse($mascota, 'Estado actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Mascota no encontrada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar estado', $e->getMessage(), 500);
        }
    }
}
