<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterinarias', function (Blueprint $table) {
            $table->id();
            $table->string('Nombre_vet');
            $table->text('descripcion')->nullable();
            $table->string('Direccion')->unique();
            $table->string('Telefono')->unique();
            $table->string('Email')->unique();
            $table->json('servicios')->nullable();
            $table->json('servicios_detallados')->nullable();
            $table->json('equipo_medico')->nullable();
            $table->string('horario_atencion')->nullable();
            $table->integer('anios_experiencia')->nullable();
            $table->boolean('urgencias_24h')->default(false);
            $table->json('convenios')->nullable();
            $table->decimal('precio_consulta', 10, 2)->nullable();
            $table->boolean('acepta_seguros')->default(false);
            $table->decimal('valoracion_promedio', 3, 2)->default(0);
            $table->integer('total_valoraciones')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('logo')->nullable();
            $table->string('logo_public_id')->nullable();
            $table->json('galeria_fotos')->nullable();
            $table->json('redes_sociales')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('sitio_web')->nullable();
            $table->boolean('verificado')->default(false);
            $table->json('documentos_verificacion')->nullable();
            $table->json('cobertura_zona')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('departamento')->nullable();

            // Ubicación
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->integer('radio_atencion')->default(10);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinarias');
    }
};
