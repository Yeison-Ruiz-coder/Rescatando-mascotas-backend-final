<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'tipo' => 'required|in:user,fundacion,veterinaria',
            'nombre_entidad' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'registro_sanitario' => 'nullable|string',
            'capacidad' => 'nullable|integer|min:0',
            'servicios' => 'nullable|array',
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

    public function checkEmail(Request $request)
    {
        $exists = $this->authService->checkEmail($request->query('email'));
        return $this->successResponse(['exists' => $exists], 'Email verificado');
    }
}
