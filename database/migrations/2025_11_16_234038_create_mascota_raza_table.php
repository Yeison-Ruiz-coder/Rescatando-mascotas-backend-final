<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascota_raza', function (Blueprint $table) {
            
            $table->unsignedBigInteger('mascota_id');
            $table->unsignedBigInteger('raza_id');
            $table->primary(['mascota_id', 'raza_id']); 

            $table->foreign('mascota_id')->references('id')->on('mascotas')->onDelete('cascade');
            $table->foreign('raza_id')->references('id')->on('razas')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascota_raza');
    }
};