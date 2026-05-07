<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Perfil y biografía
            if (!Schema::hasColumn('users', 'biografia')) {
                $table->text('biografia')->nullable()->after('apellidos');
            }

            if (!Schema::hasColumn('users', 'redes_sociales')) {
                $table->json('redes_sociales')->nullable()->after('biografia');
            }

            // Ubicación
            if (!Schema::hasColumn('users', 'pais')) {
                $table->string('pais')->nullable()->after('direccion');
            }

            if (!Schema::hasColumn('users', 'ciudad')) {
                $table->string('ciudad')->nullable()->after('pais');
            }

            if (!Schema::hasColumn('users', 'codigo_postal')) {
                $table->string('codigo_postal')->nullable()->after('ciudad');
            }

            if (!Schema::hasColumn('users', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable()->after('codigo_postal');
            }

            if (!Schema::hasColumn('users', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable()->after('lat');
            }

            // Preferencias
            if (!Schema::hasColumn('users', 'preferencias_notificaciones')) {
                $table->json('preferencias_notificaciones')->nullable()->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'idioma')) {
                $table->string('idioma')->default('es')->after('preferencias_notificaciones');
            }

            if (!Schema::hasColumn('users', 'tema')) {
                $table->string('tema')->default('light')->after('idioma');
            }

            // Actividad
            if (!Schema::hasColumn('users', 'ultimo_acceso')) {
                $table->timestamp('ultimo_acceso')->nullable()->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'ultima_ip')) {
                $table->string('ultima_ip')->nullable()->after('ultimo_acceso');
            }

            if (!Schema::hasColumn('users', 'veces_reportado')) {
                $table->integer('veces_reportado')->default(0)->after('estado');
            }

            // Verificación
            if (!Schema::hasColumn('users', 'documento_verificado')) {
                $table->boolean('documento_verificado')->default(false)->after('numero_documento');
            }

            if (!Schema::hasColumn('users', 'email_verification_token')) {
                $table->string('email_verification_token')->nullable()->after('email_verified_at');
            }

            if (!Schema::hasColumn('users', 'telefono_verificado')) {
                $table->boolean('telefono_verificado')->default(false)->after('telefono');
            }

            // Estadísticas
            if (!Schema::hasColumn('users', 'total_mascotas_adoptadas')) {
                $table->integer('total_mascotas_adoptadas')->default(0)->after('veces_reportado');
            }

            if (!Schema::hasColumn('users', 'total_donaciones')) {
                $table->decimal('total_donaciones', 10, 2)->default(0)->after('total_mascotas_adoptadas');
            }

            if (!Schema::hasColumn('users', 'puntos')) {
                $table->integer('puntos')->default(0)->after('total_donaciones');
            }

            if (!Schema::hasColumn('users', 'rango')) {
                $table->string('rango')->default('Nuevo')->after('puntos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'biografia', 'redes_sociales', 'pais', 'ciudad', 'codigo_postal', 'lat', 'lng',
                'preferencias_notificaciones', 'idioma', 'tema', 'ultimo_acceso', 'ultima_ip',
                'veces_reportado', 'documento_verificado', 'email_verification_token',
                'telefono_verificado', 'total_mascotas_adoptadas', 'total_donaciones', 'puntos', 'rango'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
