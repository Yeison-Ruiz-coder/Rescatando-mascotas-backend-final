<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();

            // Polimórfica para calificar veterinarias o fundaciones
            $table->morphs('calificable');

            // Usuario que califica
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Calificación
            $table->tinyInteger('puntuacion')->unsigned()->comment('1-5 estrellas');
            $table->text('comentario')->nullable();

            // Categorías específicas
            $table->tinyInteger('puntuacion_atencion')->unsigned()->nullable()->comment('1-5');
            $table->tinyInteger('puntuacion_profesionalismo')->unsigned()->nullable()->comment('1-5');
            $table->tinyInteger('puntuacion_instalaciones')->unsigned()->nullable()->comment('1-5');
            $table->tinyInteger('puntuacion_precio')->unsigned()->nullable()->comment('1-5');

            // Respuesta del negocio
            $table->text('respuesta')->nullable();
            $table->timestamp('fecha_respuesta')->nullable();

            // Estado
            $table->boolean('aprobada')->default(false);
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->foreignId('aprobada_por')->nullable()->constrained('users')->onDelete('set null');

            // Metadatos
            $table->json('fotos')->nullable();
            $table->boolean('anonima')->default(false);
            $table->string('ip_creacion')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['calificable_id', 'calificable_type']);
            $table->index('puntuacion');
            $table->index('aprobada');
            $table->unique(['user_id', 'calificable_id', 'calificable_type'], 'unique_valoracion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
