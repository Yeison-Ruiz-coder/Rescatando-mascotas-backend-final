<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veterinarias', function (Blueprint $table) {
            // Información general
            if (!Schema::hasColumn('veterinarias', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('Nombre_vet');
            }

            if (!Schema::hasColumn('veterinarias', 'horario_atencion')) {
                $table->string('horario_atencion')->nullable()->after('servicios');
            }

            if (!Schema::hasColumn('veterinarias', 'anios_experiencia')) {
                $table->integer('anios_experiencia')->nullable()->after('descripcion');
            }

            // Servicios más detallados
            if (!Schema::hasColumn('veterinarias', 'servicios_detallados')) {
                $table->json('servicios_detallados')->nullable()->after('servicios');
            }

            if (!Schema::hasColumn('veterinarias', 'equipo_medico')) {
                $table->json('equipo_medico')->nullable()->after('servicios_detallados');
            }

            // Imágenes y branding
            if (!Schema::hasColumn('veterinarias', 'logo')) {
                $table->string('logo')->nullable()->after('Email');
            }

            if (!Schema::hasColumn('veterinarias', 'logo_public_id')) {
                $table->string('logo_public_id')->nullable()->after('logo');
            }

            if (!Schema::hasColumn('veterinarias', 'galeria_fotos')) {
                $table->json('galeria_fotos')->nullable()->after('logo');
            }

            // Redes sociales y contacto
            if (!Schema::hasColumn('veterinarias', 'redes_sociales')) {
                $table->json('redes_sociales')->nullable()->after('telefono');
            }

            if (!Schema::hasColumn('veterinarias', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('telefono');
            }

            if (!Schema::hasColumn('veterinarias', 'sitio_web')) {
                $table->string('sitio_web')->nullable()->after('Email');
            }

            // Verificación y calidad
            if (!Schema::hasColumn('veterinarias', 'verificado')) {
                $table->boolean('verificado')->default(false)->after('urgencias_24h');
            }

            if (!Schema::hasColumn('veterinarias', 'documentos_verificacion')) {
                $table->json('documentos_verificacion')->nullable()->after('verificado');
            }

            // Precios y planes
            if (!Schema::hasColumn('veterinarias', 'precio_consulta')) {
                $table->decimal('precio_consulta', 10, 2)->nullable()->after('servicios');
            }

            if (!Schema::hasColumn('veterinarias', 'acepta_seguros')) {
                $table->boolean('acepta_seguros')->default(false)->after('precio_consulta');
            }

            // Calificaciones
            if (!Schema::hasColumn('veterinarias', 'valoracion_promedio')) {
                $table->decimal('valoracion_promedio', 3, 2)->default(0)->after('convenios');
            }

            if (!Schema::hasColumn('veterinarias', 'total_valoraciones')) {
                $table->integer('total_valoraciones')->default(0)->after('valoracion_promedio');
            }

            // Cobertura y ubicación
            if (!Schema::hasColumn('veterinarias', 'cobertura_zona')) {
                $table->json('cobertura_zona')->nullable()->after('radio_atencion');
            }

            if (!Schema::hasColumn('veterinarias', 'ciudad')) {
                $table->string('ciudad')->nullable()->after('Direccion');
            }

            if (!Schema::hasColumn('veterinarias', 'departamento')) {
                $table->string('departamento')->nullable()->after('ciudad');
            }

            // Soft deletes
            if (!Schema::hasColumn('veterinarias', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('veterinarias', function (Blueprint $table) {
            $columns = [
                'descripcion', 'horario_atencion', 'anios_experiencia', 'servicios_detallados',
                'equipo_medico', 'logo', 'logo_public_id', 'galeria_fotos', 'redes_sociales',
                'whatsapp', 'sitio_web', 'verificado', 'documentos_verificacion', 'precio_consulta',
                'acepta_seguros', 'valoracion_promedio', 'total_valoraciones', 'cobertura_zona',
                'ciudad', 'departamento', 'deleted_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('veterinarias', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
