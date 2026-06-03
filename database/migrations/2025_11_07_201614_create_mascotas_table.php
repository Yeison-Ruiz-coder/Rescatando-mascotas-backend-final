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
            $table->decimal('peso_aprox', 5, 2)->nullable();
            $table->enum('tamano', ['pequeño', 'mediano', 'grande', 'muy_grande'])->nullable();
            $table->string('color')->nullable();
            $table->enum('genero', ['Macho', 'Hembra', 'Desconocido'])->nullable();
            $table->enum('estado', ['Adoptado', 'En adopcion', 'Rescatada', 'En acogida'])->default('En adopcion');

            // Ubicación y descripción
            $table->string('lugar_rescate')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('condiciones_especiales')->nullable();
            $table->text('salud_general')->nullable();
            $table->boolean('esterilizado')->default(false);
            $table->boolean('desparasitado')->default(false);
            $table->boolean('vacunado')->default(false);
            $table->json('enfermedades_cronicas')->nullable();
            $table->json('medicamentos')->nullable();

            // Fotos
            $table->string('foto_principal')->nullable();
            $table->string('foto_principal_public_id')->nullable();
            $table->json('galeria_fotos')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_public_id')->nullable();

            // Características
            $table->boolean('necesita_hogar_temporal')->default(false);
            $table->boolean('apto_con_ninos')->default(true);
            $table->boolean('apto_con_otros_animales')->default(true);
            $table->json('requisitos_adopcion')->nullable();
            $table->string('hogar_recomendado')->nullable();

            // Fechas
            $table->date('fecha_ingreso')->nullable();
            $table->timestamp('fecha_publicacion')->nullable();
            $table->date('fecha_salida')->nullable();

            // Relación con fundación
            $table->unsignedBigInteger('fundacion_id')->nullable();
            $table->foreign('fundacion_id')->references('id')->on('fundaciones')->onDelete('set null');
            $table->foreignId('veterinaria_id')->nullable()->constrained('veterinarias')->onDelete('set null');
            $table->boolean('destacada')->default(false);
            $table->integer('vistas')->default(0);
            $table->integer('interesados')->default(0);
            $table->json('padrinos')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
