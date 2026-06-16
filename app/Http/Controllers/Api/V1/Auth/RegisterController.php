<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class RegisterController extends Controller
{
    use ApiResponses;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos básicos del usuario
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'tipo' => 'required|in:user,fundacion,veterinaria',

            // Datos de perfil adicionales (nuevos)
            'tipo_documento' => 'nullable|string|max:50',
            'numero_documento' => 'nullable|string|max:50|unique:users',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'direccion' => 'nullable|string|max:500',
            'pais' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',

            // Datos específicos para fundación/veterinaria
            'nombre_entidad' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'horario_atencion' => 'nullable|string',

            // Datos para fundación
            'registro_sanitario' => 'nullable|string|max:255',
            'capacidad' => 'nullable|integer|min:0',

            // Datos para veterinaria
            'servicios' => 'nullable|array',

            // Ubicación (geolocalización)
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $result = $this->authService->register($validator->validated());

            $message = $result['requiere_aprobacion']
                ? 'Tu solicitud de registro ha sido enviada. Un administrador la revisará pronto.'
                : 'Usuario registrado exitosamente';

            return $this->successResponse($result, $message, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar usuario', $e->getMessage(), 500);
        }
    }

    /**
     * 🔥 VERIFICAR EMAIL - CON ESTADO DEL USUARIO
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Buscar el usuario por email
        $user = User::where('email', $request->query('email'))->first();

        if (!$user) {
            return $this->successResponse([
                'exists' => false,
                'estado' => null,
                'mensaje' => 'Correo disponible para registro'
            ], 'Email verificado');
        }

        // Email existe, obtener estado
        $estado = $user->estado; // 'activo', 'pendiente', 'inactivo', 'suspendido'

        $mensaje = match($estado) {
            'activo' => 'Este correo ya está registrado y activo. Por favor, inicia sesión.',
            'pendiente' => 'Este correo ya está registrado y pendiente de aprobación por un administrador.',
            'inactivo' => 'Este correo está inactivo. Contacta con el soporte.',
            'suspendido' => 'Este correo está suspendido. Contacta con el soporte.',
            default => 'Este correo ya está registrado'
        };

        // También obtener el tipo de usuario (útil para mostrar mensajes específicos)
        $tipo = $user->tipo; // 'user', 'fundacion', 'veterinaria'

        return $this->successResponse([
            'exists' => true,
            'estado' => $estado,
            'tipo' => $tipo,
            'mensaje' => $mensaje
        ], 'Email verificado');
    }
}
