<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_solicitud', ['adopcion', 'rescate', 'apadrinamiento', 'donacion', 'otro'])->default('adopcion');
            $table->text('contenido')->nullable(); // Mensaje del solicitante
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->enum('estado', ['pendiente', 'en_revision', 'aprobada', 'rechazada', 'completada'])->default('pendiente');
            $table->text('razon_rechazo')->nullable();
            $table->text('notas_internas')->nullable(); // Para administradores

            // Solicitante (solo usuarios registrados)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nombre_solicitante')->nullable(); // Si no es usuario registrado
            $table->string('email_solicitante')->nullable();
            $table->string('telefono_solicitante')->nullable();
            $table->json('datos_adicionales')->nullable();

            // Item solicitado (polimórfico: mascota, ayuda económica, etc.)
            $table->nullableMorphs('solicitable'); // Crea solicitable_id y solicitable_type

            // Gestión interna
            $table->foreignId('revisado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_revision')->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('tipo_solicitud');
            $table->index('fecha_solicitud');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
