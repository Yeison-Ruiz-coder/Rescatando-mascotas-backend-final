<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use ApiResponses;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $result = $this->authService->login($request->only(['email', 'password']));

            return $this->successResponse($result, 'Inicio de sesión exitoso');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 401);
        }
    }
}
