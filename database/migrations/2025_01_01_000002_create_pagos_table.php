<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            // Relación con suscripción
            $table->foreignId('suscripcion_id')
                ->constrained('suscripciones')
                ->onDelete('cascade');

            // Datos del pago
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('COP');
            $table->string('metodo_pago', 50); // 'demo', 'stripe', 'paypal', 'mercadopago'

            // Estado del pago
            $table->enum('estado', [
                'pendiente',
                'completado',
                'fallido',
                'reembolsado'
            ])->default('pendiente');

            // Referencias de la pasarela
            $table->string('transaccion_id')->nullable();
            $table->string('comprobante_url')->nullable();
            $table->timestamp('fecha_pago')->nullable();

            // Identificar si es demo
            $table->boolean('es_demo')->default(true);

            // Metadatos extra (JSON)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('transaccion_id');
            $table->index('estado');
            $table->index('suscripcion_id');
            $table->index('es_demo');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
