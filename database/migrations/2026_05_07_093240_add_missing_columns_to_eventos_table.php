<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Verificar si no existen antes de agregar
            if (!Schema::hasColumn('eventos', 'fecha_fin')) {
                $table->datetime('fecha_fin')->nullable()->after('fecha_evento');
            }
            if (!Schema::hasColumn('eventos', 'capacidad_maxima')) {
                $table->integer('capacidad_maxima')->nullable()->after('fecha_fin');
            }
            if (!Schema::hasColumn('eventos', 'costo')) {
                $table->string('costo')->nullable()->after('capacidad_maxima');
            }
            if (!Schema::hasColumn('eventos', 'organizador')) {
                $table->string('organizador')->nullable()->after('costo');
            }
            if (!Schema::hasColumn('eventos', 'telefono_contacto')) {
                $table->string('telefono_contacto')->nullable()->after('organizador');
            }
            if (!Schema::hasColumn('eventos', 'email_contacto')) {
                $table->string('email_contacto')->nullable()->after('telefono_contacto');
            }
            if (!Schema::hasColumn('eventos', 'categoria')) {
                $table->string('categoria')->nullable()->after('email_contacto');
            }
            if (!Schema::hasColumn('eventos', 'tags')) {
                $table->json('tags')->nullable()->after('categoria');
            }
        });
    }

    public function down()
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_fin', 'capacidad_maxima', 'costo', 'organizador',
                'telefono_contacto', 'email_contacto', 'categoria', 'tags'
            ]);
        });
    }
};
