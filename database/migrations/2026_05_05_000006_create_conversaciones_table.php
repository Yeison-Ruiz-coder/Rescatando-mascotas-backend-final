<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conversaciones
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participante1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('participante2_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('ultimo_mensaje_at')->nullable();
            $table->text('ultimo_mensaje')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('fecha_cierre')->nullable();
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['participante1_id', 'participante2_id'], 'unique_conversacion');
            $table->index(['participante1_id', 'ultimo_mensaje_at']);
            $table->index(['participante2_id', 'ultimo_mensaje_at']);
        });

        // Mensajes
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('conversaciones')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_at')->nullable();
            $table->timestamp('entregado_at')->nullable();
            $table->json('adjuntos')->nullable();
            $table->json('reacciones')->nullable();
            $table->enum('tipo', ['texto', 'imagen', 'video', 'documento', 'ubicacion'])->default('texto');
            $table->boolean('editado')->default(false);
            $table->timestamp('editado_at')->nullable();
            $table->boolean('eliminado_para_mi')->default(false);
            $table->boolean('eliminado_para_todos')->default(false);
            $table->foreignId('respondido_a')->nullable()->constrained('mensajes')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversacion_id', 'created_at']);
            $table->index(['user_id', 'leido']);
            $table->index(['conversacion_id', 'leido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('conversaciones');
    }
};
