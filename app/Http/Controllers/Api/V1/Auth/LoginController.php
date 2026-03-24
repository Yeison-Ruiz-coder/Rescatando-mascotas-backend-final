<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // 🔥 VERIFICAR QUE EL USUARIO ESTÉ ACTIVO
        if ($user->estado !== 'activo') {
            $mensaje = match($user->estado) {
                'pendiente' => 'Tu cuenta está pendiente de aprobación por un administrador',
                'inactivo' => 'Tu cuenta está inactiva',
                'suspendido' => 'Tu cuenta ha sido suspendida',
                default => 'No puedes iniciar sesión'
            };

            return response()->json([
                'success' => false,
                'message' => $mensaje,
                'estado' => $user->estado
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'token' => $token,
                'user' => $user->load(['fundacion', 'veterinaria'])
            ]
        ]);
    }
}
