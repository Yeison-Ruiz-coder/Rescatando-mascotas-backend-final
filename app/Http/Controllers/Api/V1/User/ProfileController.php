<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Mostrar perfil del usuario autenticado
     */
    public function show(Request $request)
    {
        $user = $request->user()->load(['solicitudes', 'adopciones', 'donaciones', 'suscripciones']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['nombre', 'apellidos', 'telefono', 'direccion', 'fecha_nacimiento']);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente',
            'data' => $user
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual no es correcta'
            ], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }

    /**
     * Eliminar cuenta
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Verificar si tiene relaciones activas
        if ($user->adopciones()->whereIn('estado', ['en_proceso', 'aprobada'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu cuenta mientras tengas adopciones en proceso'
            ], 400);
        }

        if ($user->solicitudes()->whereIn('estado', ['pendiente', 'en_revision'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu cuenta mientras tengas solicitudes pendientes'
            ], 400);
        }

        // Revocar tokens
        $user->tokens()->delete();

        // Eliminar usuario (soft delete)
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta eliminada exitosamente'
        ]);
    }
}
