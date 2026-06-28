<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Services\Payment\PaymentService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use ApiResponses;

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Iniciar pago (demo o real según configuración)
     * POST /api/payment/iniciar
     */
    public function iniciarPago(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suscripcion_id' => 'required|exists:suscripciones,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        $user = $request->user();
        $suscripcion = Suscripcion::where('user_id', $user->id)
            ->findOrFail($request->suscripcion_id);

        if ($suscripcion->estado === 'activo') {
            return $this->errorResponse('Esta suscripción ya está activa', null, 400);
        }

        try {
            $resultado = $this->paymentService->iniciarPago($suscripcion, [
                'email' => $user->email,
                'nombre' => $user->nombre_completo,
            ]);

            return $this->successResponse($resultado, 'Pago iniciado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al iniciar el pago', $e->getMessage(), 500);
        }
    }

    /**
     * Confirmar pago (demo o real según configuración)
     * POST /api/payment/confirmar
     */
    public function confirmarPago(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'suscripcion_id' => 'required|exists:suscripciones,id',
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        $user = $request->user();
        $suscripcion = Suscripcion::where('user_id', $user->id)
            ->findOrFail($request->suscripcion_id);

        try {
            $resultado = $this->paymentService->confirmarPago($suscripcion, [
                'reference' => $request->reference,
            ]);

            return $this->successResponse($resultado, 'Pago confirmado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al confirmar el pago', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener el modo de pago actual
     * GET /api/payment/mode
     */
    public function getMode()
    {
        return $this->successResponse([
            'mode' => $this->paymentService->getMode(),
            'driver' => $this->paymentService->getDriver(),
            'is_demo' => $this->paymentService->isDemoMode(),
            'is_live' => $this->paymentService->isLiveMode(),
        ], 'Modo de pago obtenido');
    }
}
