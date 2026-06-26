<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adopciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_adopcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['en_proceso', 'aprobada', 'completada', 'rechazada', 'cancelada'])->default('en_proceso');
            $table->text('razon_rechazo')->nullable();
            $table->date('fecha_cierre')->nullable();

            // Relaciones clave
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Quien adopta (YA ES USUARIO)
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');
            $table->foreignId('fundacion_id')->nullable()->constrained('fundaciones')->onDelete('set null'); // Fundación responsable
            $table->foreignId('administrador_id')->nullable()->constrained('users')->onDelete('set null'); // Admin que aprueba

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adopciones');
    }
};
