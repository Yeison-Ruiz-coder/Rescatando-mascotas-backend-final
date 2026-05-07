<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fundaciones', function (Blueprint $table) {
            $table->id();
            $table->string('Nombre_1');
            $table->string('Direccion')->unique();
            $table->string('Telefono')->unique();
            $table->string('Email')->unique();
            $table->string('registro_sanitario')->nullable();
            $table->integer('capacidad_maxima')->nullable();
            $table->json('necesidades_actuales')->nullable();
            $table->string('horario_atencion')->nullable();
            $table->boolean('recibe_voluntarios')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Ubicación
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->integer('radio_atencion')->default(10);

            // Imágenes y verificación
            $table->string('imagen_portada')->nullable();
            $table->string('imagen_portada_public_id')->nullable();
            $table->boolean('verificado')->default(false);

            // Información adicional
            $table->string('ciudad')->nullable();
            $table->date('fecha_fundacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fundaciones');
    }
};
