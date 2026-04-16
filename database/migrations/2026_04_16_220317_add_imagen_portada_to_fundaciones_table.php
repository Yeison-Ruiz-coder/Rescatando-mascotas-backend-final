<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fundaciones', function (Blueprint $table) {
            // Agregar columna imagen_portada si no existe
            if (!Schema::hasColumn('fundaciones', 'imagen_portada')) {
                $table->string('imagen_portada')->nullable();
            }

            // Agregar otras columnas que puedan faltar
            if (!Schema::hasColumn('fundaciones', 'verificado')) {
                $table->boolean('verificado')->default(false);
            }

            if (!Schema::hasColumn('fundaciones', 'ciudad')) {
                $table->string('ciudad')->nullable();
            }

            if (!Schema::hasColumn('fundaciones', 'fecha_fundacion')) {
                $table->date('fecha_fundacion')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fundaciones', function (Blueprint $table) {
            $table->dropColumn([
                'imagen_portada',
                'verificado',
                'ciudad',
                'fecha_fundacion'
            ]);
        });
    }
};
