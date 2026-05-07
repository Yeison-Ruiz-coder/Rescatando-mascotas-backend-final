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
            $table->string('Direccion')->unique();
            $table->string('Telefono')->unique();
            $table->string('Email')->unique();
            $table->json('servicios')->nullable();
            $table->boolean('urgencias_24h')->default(false);
            $table->json('convenios')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Ubicación
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->integer('radio_atencion')->default(10);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinarias');
    }
};
