<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_evento');
            $table->string('lugar_evento');
            $table->text('descripcion');
            $table->datetime('fecha_evento');
            $table->datetime('fecha_fin')->nullable();
            $table->string('imagen_url')->nullable();
            $table->string('imagen_public_id')->nullable();
            $table->foreignId('fundacion_id')->nullable()->constrained('fundaciones')->onDelete('cascade');
            $table->enum('tipo', ['fundacion', 'admin'])->default('fundacion');
            $table->integer('capacidad_maxima')->nullable();
            $table->string('costo')->nullable();
            $table->string('organizador')->nullable();
            $table->string('telefono_contacto')->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('categoria')->nullable();
            $table->json('tags')->nullable();
            $table->integer('likes')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
