<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();

            // Relación con usuario
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Datos del método de pago
            $table->string('tipo', 50); // 'tarjeta', 'paypal', 'mercado_pago'
            $table->string('token', 255); // Token de la pasarela

            // Datos de la tarjeta (si aplica)
            $table->string('ultimos_digitos', 4)->nullable();
            $table->string('marca', 50)->nullable(); // 'visa', 'mastercard', 'amex'
            $table->integer('expiracion_mes')->nullable();
            $table->integer('expiracion_anio')->nullable();

            // Datos de PayPal (si aplica)
            $table->string('paypal_email')->nullable();
            $table->string('paypal_id')->nullable();

            $table->boolean('es_principal')->default(false);
            $table->boolean('es_demo')->default(true);

            $table->timestamps();

            $table->index('user_id');
            $table->index('token');
            $table->unique(['user_id', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_pago');
    }
};
