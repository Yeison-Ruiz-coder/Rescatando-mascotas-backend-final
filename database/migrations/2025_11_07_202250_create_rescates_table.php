<?php
// database/migrations/2025_11_07_202250_create_rescates_table.php (reemplazar completamente)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('rescates');

        Schema::create('rescates', function (Blueprint $table) {
            $table->id();

            // Datos del rescate
            $table->date('fecha_rescate');
            $table->string('lugar_rescate');
            $table->text('descripcion_rescate');

            // Estado y clasificación
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'seguimiento'])
                  ->default('pendiente');
            $table->enum('tipo_emergencia', ['herido', 'abandonado', 'urgente', 'otro'])
                  ->nullable();
            $table->enum('prioridad', ['alta', 'media', 'baja'])->nullable();

            // Geolocalización
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            // Datos del reportante (soporte anónimo)
            $table->string('nombre_reportante')->nullable();
            $table->string('email_reportante')->nullable();
            $table->string('telefono_reportante')->nullable();

            // Relaciones
            $table->foreignId('mascota_id')->nullable()->constrained('mascotas')->onDelete('set null');
            $table->foreignId('reporte_id')->nullable()->constrained('reportes')->onDelete('set null');
            $table->foreignId('usuario_reporto_id')->nullable()->constrained('users')->onDelete('set null');

            // Relación polimórfica para entidad responsable (fundación/veterinaria)
            $table->nullableMorphs('entidad_responsable');

            // Admin que gestionó
            $table->foreignId('gestionado_por')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Índices
            $table->index('estado');
            $table->index('tipo_emergencia');
            $table->index('prioridad');
            $table->index('fecha_rescate');
            $table->index(['entidad_responsable_id', 'entidad_responsable_type']);
            $table->index(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescates');
    }
};
