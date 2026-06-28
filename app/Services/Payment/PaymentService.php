<?php

namespace App\Services\Payment;

use App\Models\Suscripcion;
use App\Models\Pago;
use Illuminate\Support\Str;

class PaymentService
{
    protected $mode;
    protected $driver;

    public function __construct()
    {
        $this->mode = config('services.payment.mode', 'demo');
        $this->driver = config('services.payment.driver', 'stripe');
    }

    /**
     * Iniciar un pago (demo o real según configuración)
     */
    public function iniciarPago(Suscripcion $suscripcion, array $datosCliente = [])
    {
        if ($this->mode === 'demo') {
            return $this->iniciarPagoDemo($suscripcion);
        }

        // 🔥 Modo real - Aquí iría la integración con Stripe/PayPal
        // Por ahora solo simulamos
        return $this->iniciarPagoDemo($suscripcion);
    }

    /**
     * Confirmar un pago (demo o real según configuración)
     */
    public function confirmarPago(Suscripcion $suscripcion, array $datos)
    {
        if ($this->mode === 'demo') {
            return $this->confirmarPagoDemo($suscripcion, $datos);
        }

        // 🔥 Modo real - Aquí iría la confirmación con Stripe/PayPal
        return $this->confirmarPagoDemo($suscripcion, $datos);
    }

    /**
     * 🎭 MODO DEMO - Simular pago
     */
    protected function iniciarPagoDemo(Suscripcion $suscripcion)
    {
        $reference = 'DEMO-' . strtoupper(Str::random(12));

        // Crear pago pendiente
        $pago = Pago::create([
            'suscripcion_id' => $suscripcion->id,
            'monto' => $suscripcion->monto_mensual,
            'moneda' => 'COP',
            'metodo_pago' => 'demo',
            'estado' => 'pendiente',
            'transaccion_id' => $reference,
            'es_demo' => true,
        ]);

        return [
            'success' => true,
            'mode' => 'demo',
            'reference' => $reference,
            'suscripcion_id' => $suscripcion->id,
            'pago_id' => $pago->id,
            'monto' => $suscripcion->monto_mensual,
            'mensaje' => 'Simulando pago en modo demostración',
        ];
    }

    /**
     * 🎭 MODO DEMO - Confirmar pago
     */
    protected function confirmarPagoDemo(Suscripcion $suscripcion, array $datos)
    {
        // Simular procesamiento
        usleep(rand(500000, 1500000));

        // Buscar el pago pendiente
        $pago = Pago::where('suscripcion_id', $suscripcion->id)
            ->where('estado', 'pendiente')
            ->where('transaccion_id', $datos['reference'])
            ->first();

        if (!$pago) {
            throw new \Exception('Pago no encontrado');
        }

        // Actualizar pago
        $pago->update([
            'estado' => 'completado',
            'fecha_pago' => now(),
        ]);

        // Activar suscripción
        $suscripcion->update([
            'estado' => 'activo',
            'fecha_inicio' => now(),
            'es_demo' => true,
            'payment_method' => 'demo',
            'payment_reference' => $datos['reference'],
        ]);

        return [
            'success' => true,
            'mode' => 'demo',
            'pago' => $pago,
            'suscripcion' => $suscripcion,
            'mensaje' => '✅ Pago simulado exitosamente (Modo Demo)',
        ];
    }

    public function isDemoMode(): bool
    {
        return $this->mode === 'demo';
    }

    public function isLiveMode(): bool
    {
        return $this->mode === 'live';
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}
