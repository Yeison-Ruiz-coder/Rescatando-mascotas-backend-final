<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('accion'); // created, updated, deleted, restored, login, etc.
            $table->string('tabla');
            $table->unsignedBigInteger('registro_id');
            $table->text('descripcion')->nullable();
            $table->json('valores_viejos')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->json('cambios')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['tabla', 'registro_id']);
            $table->index('user_id');
            $table->index('accion');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
