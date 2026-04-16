<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evento_asistentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('estado', ['confirmado', 'cancelado'])->default('confirmado');
            $table->timestamps();

            $table->unique(['evento_id', 'user_id']); // Evitar duplicados
        });
    }

    public function down()
    {
        Schema::dropIfExists('evento_asistentes');
    }
};
