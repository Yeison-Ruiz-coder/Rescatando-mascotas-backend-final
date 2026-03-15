<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascota_vacuna', function (Blueprint $table) {
            
            $table->unsignedBigInteger('mascota_id');
            $table->unsignedBigInteger('tipos_vacunas_id');
            $table->date('fecha_aplicacion')->nullable();
            $table->primary(['mascota_id', 'tipos_vacunas_id', 'fecha_aplicacion']);

            $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('cascade');
            $table->foreign('tipos_vacunas_id')->references('id')->on('tipos_vacunas')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascota_vacuna');
    }
};