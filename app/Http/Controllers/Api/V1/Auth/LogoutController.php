<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use ApiResponses;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Sesión cerrada exitosamente');
    }
}
