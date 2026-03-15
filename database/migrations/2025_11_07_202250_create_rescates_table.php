<?php
// database/migrations/2025_11_07_202250_create_rescates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescates', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_rescate');
            $table->string('lugar_rescate');
            $table->text('descripcion_rescate');
            $table->enum('estado', ['en_proceso', 'completado', 'seguimiento'])->default('en_proceso');

            // Relaciones principales
            $table->foreignId('mascota_id')->nullable()->constrained('mascotas')->onDelete('set null');
            $table->foreignId('reporte_id')->nullable()->constrained('reportes')->onDelete('set null');

            // 👇 UNIFICADO: Usuario que reportó (antes eran 2 campos)
            $table->foreignId('usuario_reporto_id')->nullable()->constrained('users')->onDelete('set null');

            // 👇 RELACIÓN POLIMÓRFICA para entidades involucradas (veterinaria/fundacion)
            $table->nullableMorphs('entidad_responsable'); // Crea entidad_responsable_id y entidad_responsable_type

            // 👇 Admin que gestiona
            $table->foreignId('gestionado_por')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Índices
            $table->index('estado');
            $table->index('fecha_rescate');
            $table->index(['entidad_responsable_id', 'entidad_responsable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescates');
    }
};
