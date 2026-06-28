<?php
// database/migrations/2025_11_07_201954_create_suscripciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');
            $table->decimal('monto_mensual', 10, 2);
            $table->enum('frecuencia', ['unica', 'mensual', 'trimestral', 'anual'])->default('mensual');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('mensaje_apoyo')->nullable();
            $table->enum('estado', ['activo', 'pausado', 'cancelado', 'finalizado' , 'pendiente'])->default('activo');
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
