<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Entity\RegistrarMascotaRescateRequest;
use App\Services\Entity\RescateEntityService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class RescateController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected RescateEntityService $rescateService;

    public function __construct(RescateEntityService $rescateService)
    {
        $this->rescateService = $rescateService;
    }

    public function index(Request $request)
    {
        try {
            $rescates = $this->rescateService->getMisRescates();
            return $this->successResponse($rescates, 'Rescates obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function show(int $id)
    {
        try {
            $rescate = $this->rescateService->findById($id);
            return $this->successResponse($rescate, 'Rescate obtenido exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        }
    }

    public function completar(int $id)
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
            // ✅ Verificar que el usuario tenga una entidad asociada
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', null, 401);
            }

            // ✅ Verificar si el usuario tiene fundacion o veterinaria
            $tieneEntidad = false;
            if ($user->tipo === 'fundacion' && $user->fundacion) {
                $tieneEntidad = true;
            } elseif ($user->tipo === 'veterinaria' && $user->veterinaria) {
                $tieneEntidad = true;
            }

            if (!$tieneEntidad) {
                return $this->successResponse([], 'El usuario no tiene entidad asociada');
            }

            $rescates = $this->rescateService->getRescatesDisponibles($request);
            return $this->successResponse($rescates, 'Rescates disponibles obtenidos exitosamente');
        } catch (\Exception $e) {
            // ✅ Mostrar el error real
            Log::error('❌ Error en disponibles:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function aceptar(int $id)
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

    public function rechazar(int $id)
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

    public function registrarMascota(RegistrarMascotaRescateRequest $request, int $id)
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
                fn() => $this->rescateService->registrarMascotaDesdeRescate(
                    $id,
                    $request->validated(),
                    $files
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

    public function agregarFotos(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'fotos' => 'required|array',
            'fotos.*' => 'image|max:5120'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->agregarFotos($id, $request->file('fotos')),
                'Error al agregar fotos'
            );

            return $this->successResponse($rescate, 'Fotos agregadas exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al agregar fotos', $e->getMessage(), 500);
        }
    }

    public function actualizarEstado(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,en_proceso,completado,seguimiento'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $rescate = $this->runInTransaction(
                fn() => $this->rescateService->updateEstado($id, $request->estado),
                'Error al actualizar estado'
            );

            return $this->successResponse($rescate, 'Estado actualizado exitosamente');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Rescate no encontrado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el estado', $e->getMessage(), 500);
        }
    }
}
