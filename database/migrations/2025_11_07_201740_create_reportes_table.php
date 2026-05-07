<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_reporte', ['perdido', 'encontrado', 'maltrato', 'otro'])->default('perdido');
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('ubicacion');
            $table->date('fecha_incidente');
            $table->string('especie')->nullable();
            $table->string('raza')->nullable();
            $table->string('color')->nullable();
            $table->string('foto_url')->nullable();
            $table->json('galeria_fotos')->nullable();
            $table->enum('estado', ['activo', 'resuelto', 'cerrado'])->default('activo');

            // Persona que reporta
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nombre_reportante')->nullable();
            $table->string('telefono_reportante')->nullable();
            $table->string('email_reportante')->nullable();

            // Gestión interna
            $table->text('solucion')->nullable();
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_resolucion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_reporte');
            $table->index('estado');
            $table->index('fecha_incidente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
