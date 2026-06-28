<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            // Relación con suscripción
            $table->foreignId('suscripcion_id')
                ->constrained('suscripciones')
                ->onDelete('cascade');

            // Datos de la factura
            $table->string('numero_factura', 50)->unique();
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('COP');

            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();

            // Estado
            $table->enum('estado', [
                'pendiente',
                'pagada',
                'vencida',
                'cancelada'
            ])->default('pendiente');

            // URL del PDF
            $table->string('pdf_url')->nullable();

            // Datos del cliente (copia en el momento)
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_email')->nullable();
            $table->string('cliente_documento')->nullable();

            $table->boolean('es_demo')->default(true);
            $table->timestamps();

            $table->index('numero_factura');
            $table->index('estado');
            $table->index('suscripcion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
