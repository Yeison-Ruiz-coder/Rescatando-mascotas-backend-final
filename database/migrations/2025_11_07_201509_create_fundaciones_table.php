<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fundaciones', function (Blueprint $table) {
            $table->id();
            $table->string('Nombre_1');
            $table->string('Direccion')->unique();
            $table->string('Telefono')->unique();
            $table->string('Email')->unique();
            $table->string('registro_sanitario')->nullable(); // NIT o registro
            $table->integer('capacidad_maxima')->nullable();
            $table->json('necesidades_actuales')->nullable(); // ["alimento", "medicinas", "voluntarios"]
            $table->string('horario_atencion')->nullable();
            $table->boolean('recibe_voluntarios')->default(false);


            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fundaciones');
    }
};
