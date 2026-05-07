<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('nombre_mascota');
            $table->string('especie')->nullable();
            $table->decimal('edad_aprox', 5, 2)->nullable();
            $table->enum('genero', ['Macho', 'Hembra', 'Desconocido'])->nullable();
            $table->enum('estado', ['Adoptado', 'En adopcion', 'Rescatada', 'En acogida'])->default('En adopcion');

            // Ubicación y descripción
            $table->string('lugar_rescate')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('condiciones_especiales')->nullable();

            // Fotos
            $table->string('foto_principal')->nullable();
            $table->string('foto_principal_public_id')->nullable();
            $table->json('galeria_fotos')->nullable();

            // Características
            $table->boolean('necesita_hogar_temporal')->default(false);
            $table->boolean('apto_con_ninos')->default(true);
            $table->boolean('apto_con_otros_animales')->default(true);

            // Fechas
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_salida')->nullable();

            // Relación con fundación
            $table->unsignedBigInteger('fundacion_id')->nullable();
            $table->foreign('fundacion_id')->references('id')->on('fundaciones')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
