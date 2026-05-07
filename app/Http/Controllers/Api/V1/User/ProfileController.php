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

    public function show(Request $request)
    {
        $profile = $this->profileService->getProfile($request->user());
        return $this->successResponse($profile, 'Perfil obtenido exitosamente');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos básicos
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'avatar' => 'nullable|image|max:2048',

            // Nuevos campos de perfil
            'biografia' => 'nullable|string|max:1000',
            'redes_sociales' => 'nullable|array',
            'redes_sociales.facebook' => 'nullable|url',
            'redes_sociales.instagram' => 'nullable|string|max:255',
            'redes_sociales.twitter' => 'nullable|url',
            'redes_sociales.linkedin' => 'nullable|url',

            // Ubicación
            'pais' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',

            // Preferencias
            'idioma' => 'nullable|string|in:es,en',
            'tema' => 'nullable|string|in:light,dark,system',
            'preferencias_notificaciones' => 'nullable|array',
            'preferencias_notificaciones.email' => 'nullable|boolean',
            'preferencias_notificaciones.push' => 'nullable|boolean',
            'preferencias_notificaciones.sms' => 'nullable|boolean',
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

    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idioma' => 'nullable|string|in:es,en',
            'tema' => 'nullable|string|in:light,dark,system',
            'preferencias_notificaciones' => 'nullable|array',
            'preferencias_notificaciones.email' => 'nullable|boolean',
            'preferencias_notificaciones.push' => 'nullable|boolean',
            'preferencias_notificaciones.sms' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updatePreferences(
                    $request->user(),
                    $request->only(['idioma', 'tema', 'preferencias_notificaciones'])
                ),
                'Error al actualizar preferencias'
            );

            return $this->successResponse($user, 'Preferencias actualizadas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar preferencias', $e->getMessage(), 500);
        }
    }
}
