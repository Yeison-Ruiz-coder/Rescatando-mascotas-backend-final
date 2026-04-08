<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'tipo' => 'required|in:user,fundacion,veterinaria',

            // Campos para fundación
            'nombre_entidad' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'registro_sanitario' => 'nullable|string',
            'capacidad' => 'nullable|integer|min:0',

            // Campos para veterinaria
            'servicios' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'tipo' => $request->tipo,
            'estado' => 'activo', // Por defecto activo, pero se puede cambiar a 'pendiente' si se requiere aprobación
        ]);

        // Crear registro según el tipo
        if ($request->tipo === 'fundacion') {
            $nombreEntidad = $request->nombre_entidad ?? $request->nombre;
            Fundacion::create([
                'Nombre_1' => $nombreEntidad,
                'Direccion' => $request->direccion,
                'Telefono' => $request->telefono,
                'Email' => $request->email,
                'registro_sanitario' => $request->registro_sanitario,
                'capacidad_maxima' => $request->capacidad,
                'user_id' => $user->id,
            ]);
        } elseif ($request->tipo === 'veterinaria') {
            $nombreEntidad = $request->nombre_entidad ?? $request->nombre;
            Veterinaria::create([
                'Nombre_vet' => $nombreEntidad,
                'Direccion' => $request->direccion,
                'Telefono' => $request->telefono,
                'Email' => $request->email,
                'servicios' => $request->servicios ? json_encode($request->servicios) : null,
                'user_id' => $user->id,
            ]);
        }

        // 🔥 IMPORTANTE: Solo generar token si el usuario está ACTIVO
        $token = null;
        $message = 'Usuario registrado exitosamente';

        if ($request->tipo === 'user') {
            $token = $user->createToken('auth_token')->plainTextToken;
            $message = 'Usuario registrado exitosamente';
        } else {
            $message = 'Tu solicitud de registro ha sido enviada. Un administrador la revisará pronto.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'token' => $token,
                'user' => $user->load(['fundacion', 'veterinaria']),
                'requiere_aprobacion' => $request->tipo !== 'user'
            ]
        ], 201);
    }

    public function checkEmail(Request $request)
    {
        $email = $request->query('email');
        $exists = User::where('email', $email)->exists();

        return response()->json(['exists' => $exists]);
    }
}
