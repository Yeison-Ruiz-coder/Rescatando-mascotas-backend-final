<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\User\ProfileUserService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected ProfileUserService $profileService;

    public function __construct(ProfileUserService $profileService)
    {
        $this->profileService = $profileService;
    }

    // ========== MÉTODOS EXISTENTES (NO TOCAR) ==========

    public function show(Request $request)
    {
        $profile = $this->profileService->getProfile($request->user());
        return $this->successResponse($profile, 'Perfil obtenido exitosamente');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'avatar' => 'nullable|image|max:2048',
            'biografia' => 'nullable|string|max:1000',
            'redes_sociales' => 'nullable|array',
            'redes_sociales.facebook' => 'nullable|url',
            'redes_sociales.instagram' => 'nullable|string|max:255',
            'redes_sociales.twitter' => 'nullable|url',
            'redes_sociales.linkedin' => 'nullable|url',
            'pais' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'idioma' => 'nullable|string|in:es,en',
            'tema' => 'nullable|string|in:light,dark,system',
            'preferencias_notificaciones' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updateProfile(
                    $request->user(),
                    $request->only([
                        'nombre', 'apellidos', 'telefono', 'direccion', 'fecha_nacimiento',
                        'biografia', 'redes_sociales', 'pais', 'ciudad', 'codigo_postal',
                        'idioma', 'tema', 'preferencias_notificaciones'
                    ]),
                    $request->file('avatar')
                ),
                'Error al actualizar perfil'
            );

            return $this->successResponse($user, 'Perfil actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar perfil', $e->getMessage(), 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $this->runInTransaction(
                fn() => $this->profileService->changePassword(
                    $request->user(),
                    $request->current_password,
                    $request->new_password
                ),
                'Error al cambiar contraseña'
            );

            return $this->successResponse(null, 'Contraseña actualizada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->runInTransaction(
                fn() => $this->profileService->deleteAccount($request->user()),
                'Error al eliminar cuenta'
            );

            return $this->successResponse(null, 'Cuenta eliminada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    // ========== NUEVOS MÉTODOS (AGREGAR AL FINAL) ==========

    /**
     * Subir/actualizar avatar (endpoint dedicado)
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048|dimensions:min_width=100,min_height=100'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updateAvatar($request->user(), $request->file('avatar')),
                'Error al actualizar avatar'
            );

            return $this->successResponse([
                'avatar' => $user->avatar,
                'avatar_public_id' => $user->avatar_public_id
            ], 'Avatar actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar avatar', $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar avatar
     */
    public function deleteAvatar(Request $request)
    {
        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->deleteAvatar($request->user()),
                'Error al eliminar avatar'
            );

            return $this->successResponse(null, 'Avatar eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar avatar', $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar ubicación (lat/lng)
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'direccion' => 'nullable|string|max:500',
            'pais' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updateLocation(
                    $request->user(),
                    $request->only(['lat', 'lng', 'direccion', 'pais', 'ciudad', 'codigo_postal'])
                ),
                'Error al actualizar ubicación'
            );

            return $this->successResponse($user, 'Ubicación actualizada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar ubicación', $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar solo redes sociales
     */
    public function updateSocialNetworks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updateSocialNetworks(
                    $request->user(),
                    $request->only(['facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok'])
                ),
                'Error al actualizar redes sociales'
            );

            return $this->successResponse($user, 'Redes sociales actualizadas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar redes sociales', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener progreso de completado del perfil
     */
    public function getCompletionStatus(Request $request)
    {
        $status = $this->profileService->getCompletionStatus($request->user());
        return $this->successResponse($status, 'Estado de completado del perfil');
    }

    /**
     * Enviar código de verificación al teléfono
     */
    public function sendPhoneVerification(Request $request)
    {
        try {
            $result = $this->profileService->sendPhoneVerification($request->user());
            return $this->successResponse($result, 'Código enviado al teléfono');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Confirmar código de verificación de teléfono
     */
    public function confirmPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->confirmPhone($request->user(), $request->code),
                'Error al verificar código'
            );

            return $this->successResponse($user, 'Teléfono verificado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}
