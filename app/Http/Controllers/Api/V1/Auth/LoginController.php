<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Fundacion;
use App\Models\Veterinaria;
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

        // 🔥 AUTO-REPARACIÓN: Si es fundación y no tiene perfil, lo crea automáticamente
        if ($user->tipo === 'fundacion') {
            $fundacion = Fundacion::where('user_id', $user->id)->first();

            if (!$fundacion) {
                // Crear perfil automáticamente con datos del usuario
                $fundacion = Fundacion::create([
                    'Nombre_1' => $user->nombre ?? 'Fundación ' . $user->email,
                    'Direccion' => $user->direccion ?? 'Pendiente de actualizar',
                    'Telefono' => $user->telefono ?? '000000000',
                    'Email' => $user->email,
                    'registro_sanitario' => 'AUTO_' . time() . '_' . $user->id,
                    'capacidad_maxima' => null,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 🔥 AUTO-REPARACIÓN: Si es veterinaria y no tiene perfil, lo crea automáticamente
        if ($user->tipo === 'veterinaria') {
            $veterinaria = Veterinaria::where('user_id', $user->id)->first();

            if (!$veterinaria) {
                $veterinaria = Veterinaria::create([
                    'Nombre_vet' => $user->nombre ?? 'Veterinaria ' . $user->email,
                    'Direccion' => $user->direccion ?? 'Pendiente',
                    'Telefono' => $user->telefono ?? '000000000',
                    'Email' => $user->email,
                    'user_id' => $user->id,
                ]);
            }
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
