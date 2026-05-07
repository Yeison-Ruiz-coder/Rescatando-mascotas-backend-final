<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            // Características físicas
            if (!Schema::hasColumn('mascotas', 'peso_aprox')) {
                $table->decimal('peso_aprox', 5, 2)->nullable()->after('edad_aprox');
            }

            if (!Schema::hasColumn('mascotas', 'tamano')) {
                $table->enum('tamano', ['pequeño', 'mediano', 'grande', 'muy_grande'])->nullable()->after('peso_aprox');
            }

            if (!Schema::hasColumn('mascotas', 'color')) {
                $table->string('color')->nullable()->after('tamano');
            }

            // Salud
            if (!Schema::hasColumn('mascotas', 'salud_general')) {
                $table->text('salud_general')->nullable()->after('condiciones_especiales');
            }

            if (!Schema::hasColumn('mascotas', 'esterilizado')) {
                $table->boolean('esterilizado')->default(false)->after('salud_general');
            }

            if (!Schema::hasColumn('mascotas', 'desparasitado')) {
                $table->boolean('desparasitado')->default(false)->after('esterilizado');
            }

            if (!Schema::hasColumn('mascotas', 'vacunado')) {
                $table->boolean('vacunado')->default(false)->after('desparasitado');
            }

            if (!Schema::hasColumn('mascotas', 'enfermedades_cronicas')) {
                $table->json('enfermedades_cronicas')->nullable()->after('salud_general');
            }

            if (!Schema::hasColumn('mascotas', 'medicamentos')) {
                $table->json('medicamentos')->nullable()->after('enfermedades_cronicas');
            }

            // Requisitos para adopción
            if (!Schema::hasColumn('mascotas', 'requisitos_adopcion')) {
                $table->json('requisitos_adopcion')->nullable()->after('apto_con_otros_animales');
            }

            if (!Schema::hasColumn('mascotas', 'hogar_recomendado')) {
                $table->string('hogar_recomendado')->nullable()->after('requisitos_adopcion');
            }

            // Multimedia
            if (!Schema::hasColumn('mascotas', 'video_url')) {
                $table->string('video_url')->nullable()->after('galeria_fotos');
            }

            if (!Schema::hasColumn('mascotas', 'video_public_id')) {
                $table->string('video_public_id')->nullable()->after('video_url');
            }

            // Destacado y visibilidad
            if (!Schema::hasColumn('mascotas', 'destacada')) {
                $table->boolean('destacada')->default(false)->after('estado');
            }

            if (!Schema::hasColumn('mascotas', 'fecha_publicacion')) {
                $table->timestamp('fecha_publicacion')->nullable()->after('fecha_ingreso');
            }

            if (!Schema::hasColumn('mascotas', 'vistas')) {
                $table->integer('vistas')->default(0)->after('destacada');
            }

            if (!Schema::hasColumn('mascotas', 'interesados')) {
                $table->integer('interesados')->default(0)->after('vistas');
            }

            // Relaciones adicionales
            if (!Schema::hasColumn('mascotas', 'veterinaria_id')) {
                $table->foreignId('veterinaria_id')->nullable()->constrained('veterinarias')->onDelete('set null')->after('fundacion_id');
            }

            if (!Schema::hasColumn('mascotas', 'padrinos')) {
                $table->json('padrinos')->nullable()->after('interesados');
            }

            // Campos de auditoría
            if (!Schema::hasColumn('mascotas', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('mascotas', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            }

            // Soft deletes
            if (!Schema::hasColumn('mascotas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $columns = [
                'peso_aprox', 'tamano', 'color', 'salud_general', 'esterilizado', 'desparasitado',
                'vacunado', 'enfermedades_cronicas', 'medicamentos', 'requisitos_adopcion',
                'hogar_recomendado', 'video_url', 'video_public_id', 'destacada', 'fecha_publicacion',
                'vistas', 'interesados', 'veterinaria_id', 'padrinos', 'created_by', 'updated_by', 'deleted_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('mascotas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
