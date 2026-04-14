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
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updateProfile(
                    $request->user(),
                    $request->only(['nombre', 'apellidos', 'telefono', 'direccion', 'fecha_nacimiento']),
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
}
