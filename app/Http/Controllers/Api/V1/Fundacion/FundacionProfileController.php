<?php
// app/Http/Controllers/Api/V1/Fundacion/FundacionProfileController.php

namespace App\Http\Controllers\Api\V1\Fundacion;

use App\Http\Controllers\Controller;
use App\Services\Fundacion\FundacionProfileService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FundacionProfileController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected FundacionProfileService $profileService;

    public function __construct(FundacionProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Obtener perfil completo de la fundación (users + datos específicos)
     */
    public function show(Request $request)
    {
        $profile = $this->profileService->getCompleteProfile($request->user());
        return $this->successResponse($profile, 'Perfil obtenido exitosamente');
    }

    /**
     * Actualizar perfil completo (incluye datos de users y fundacion)
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos de User
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'biografia' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',

            // Datos específicos de Fundación
            'Nombre_1' => 'sometimes|string|max:255',
            'Direccion' => 'nullable|string|max:500',
            'Email' => 'nullable|email',
            'registro_sanitario' => 'nullable|string|max:100',
            'capacidad_maxima' => 'nullable|integer|min:0',
            'necesidades_actuales' => 'nullable|array',
            'horario_atencion' => 'nullable|string|max:500',
            'recibe_voluntarios' => 'nullable|boolean',
            'ciudad' => 'nullable|string|max:100',
            'fecha_fundacion' => 'nullable|date',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radio_atencion' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updateCompleteProfile(
                    $request->user(),
                    $request->all(),
                    $request->file('avatar')
                ),
                'Error al actualizar perfil'
            );

            return $this->successResponse($user, 'Perfil actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar perfil', $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar solo información general de la fundación
     */
    public function updateGeneralInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Nombre_1' => 'sometimes|string|max:255',
            'Direccion' => 'nullable|string|max:500',
            'Email' => 'nullable|email',
            'registro_sanitario' => 'nullable|string|max:100',
            'capacidad_maxima' => 'nullable|integer|min:0',
            'ciudad' => 'nullable|string|max:100',
            'fecha_fundacion' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $fundacion = $this->profileService->updateGeneralInfo(
                $request->user(),
                $request->all()
            );

            return $this->successResponse($fundacion, 'Información actualizada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar necesidades de la fundación
     */
    public function updateNeeds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'necesidades_actuales' => 'required|array',
            'necesidades_actuales.*' => 'string|max:100'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $fundacion = $this->profileService->updateNeeds(
                $request->user(),
                $request->necesidades_actuales
            );

            return $this->successResponse($fundacion, 'Necesidades actualizadas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar horario de atención
     */
    public function updateSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'horario_atencion' => 'required|string|max:500',
            'recibe_voluntarios' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $fundacion = $this->profileService->updateSchedule(
                $request->user(),
                $request->horario_atencion,
                $request->recibe_voluntarios ?? false
            );

            return $this->successResponse($fundacion, 'Horario actualizado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    /**
     * Subir imagen de portada
     */
    public function uploadCoverImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imagen_portada' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $fundacion = $this->profileService->uploadCoverImage(
                $request->user(),
                $request->file('imagen_portada')
            );

            return $this->successResponse([
                'imagen_portada' => $fundacion->imagen_portada,
                'imagen_portada_public_id' => $fundacion->imagen_portada_public_id
            ], 'Portada actualizada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar imagen de portada
     */
    public function deleteCoverImage(Request $request)
    {
        try {
            $fundacion = $this->profileService->deleteCoverImage($request->user());
            return $this->successResponse(null, 'Portada eliminada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }
}
