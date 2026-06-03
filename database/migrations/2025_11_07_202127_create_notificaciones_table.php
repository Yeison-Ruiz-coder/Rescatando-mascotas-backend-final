<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo')->nullable();
            $table->text('contenido');
            $table->string('tipo')->default('info');
            $table->string('icono')->nullable();
            $table->string('color')->nullable();
            $table->string('url_accion')->nullable();
            $table->string('texto_accion')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_en')->nullable();
            $table->timestamp('expira_en')->nullable();
            $table->boolean('enviada_email')->default(false);
            $table->boolean('enviada_push')->default(false);

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('creado_por_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
