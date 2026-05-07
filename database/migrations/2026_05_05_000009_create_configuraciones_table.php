<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->enum('tipo', ['string', 'integer', 'boolean', 'json', 'array'])->default('string');
            $table->string('grupo')->default('general');
            $table->string('subgrupo')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('publica')->default(false);
            $table->boolean('editable')->default(true);
            $table->json('opciones')->nullable(); // Para valores permitidos
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index(['grupo', 'subgrupo']);
            $table->index('publica');
        });

        // Insertar configuraciones por defecto
         DB::table('configuraciones')->insert([
            ['clave' => 'app_name', 'valor' => '"Huellas Felices"', 'tipo' => 'string', 'grupo' => 'general', 'publica' => true],
            ['clave' => 'max_fotos_mascota', 'valor' => '10', 'tipo' => 'integer', 'grupo' => 'mascotas', 'publica' => true],
            ['clave' => 'dias_seguimiento_adopcion', 'valor' => '30', 'tipo' => 'integer', 'grupo' => 'adopciones', 'publica' => false],
            ['clave' => 'monto_minimo_donacion', 'valor' => '5000', 'tipo' => 'integer', 'grupo' => 'donaciones', 'publica' => true],
            ['clave' => 'tiempo_maximo_rescate', 'valor' => '48', 'tipo' => 'integer', 'grupo' => 'rescates', 'publica' => false],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
