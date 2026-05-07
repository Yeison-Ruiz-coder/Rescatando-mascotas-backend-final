<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            // Geolocalización avanzada
            if (!Schema::hasColumn('reportes', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable()->after('ubicacion');
            }

            if (!Schema::hasColumn('reportes', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable()->after('lat');
            }

            if (!Schema::hasColumn('reportes', 'direccion_completa')) {
                $table->text('direccion_completa')->nullable()->after('ubicacion');
            }

            // Gestión de fotos mejorada
            if (!Schema::hasColumn('reportes', 'fotos_detalle')) {
                $table->json('fotos_detalle')->nullable()->after('galeria_fotos');
            }

            if (!Schema::hasColumn('reportes', 'fotos_public_ids')) {
                $table->json('fotos_public_ids')->nullable()->after('fotos_detalle');
            }

            // Información adicional del reporte
            if (!Schema::hasColumn('reportes', 'contacto_permiso')) {
                $table->boolean('contacto_permiso')->default(true)->after('email_reportante');
            }

            if (!Schema::hasColumn('reportes', 'anonimo')) {
                $table->boolean('anonimo')->default(false)->after('contacto_permiso');
            }

            if (!Schema::hasColumn('reportes', 'urgencia')) {
                $table->enum('urgencia', ['baja', 'media', 'alta', 'critica'])->default('media')->after('estado');
            }

            // Seguimiento interno
            if (!Schema::hasColumn('reportes', 'seguimiento_interno')) {
                $table->json('seguimiento_interno')->nullable()->after('solucion');
            }

            if (!Schema::hasColumn('reportes', 'asignado_a')) {
                $table->foreignId('asignado_a')->nullable()->constrained('users')->onDelete('set null')->after('resuelto_por');
            }

            if (!Schema::hasColumn('reportes', 'fecha_asignacion')) {
                $table->timestamp('fecha_asignacion')->nullable()->after('asignado_a');
            }

            // Campos para casos de maltrato
            if (!Schema::hasColumn('reportes', 'entidad_encargada')) {
                $table->string('entidad_encargada')->nullable()->after('seguimiento_interno');
            }

            if (!Schema::hasColumn('reportes', 'numero_caso')) {
                $table->string('numero_caso')->nullable()->unique()->after('entidad_encargada');
            }

            if (!Schema::hasColumn('reportes', 'acciones_tomadas')) {
                $table->text('acciones_tomadas')->nullable()->after('solucion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $columns = [
                'lat', 'lng', 'direccion_completa', 'fotos_detalle', 'fotos_public_ids',
                'contacto_permiso', 'anonimo', 'urgencia', 'seguimiento_interno', 'asignado_a',
                'fecha_asignacion', 'entidad_encargada', 'numero_caso', 'acciones_tomadas'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('reportes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
