<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidos')->nullable();
            $table->text('biografia')->nullable();
            $table->json('redes_sociales')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('tipo', ['admin', 'user', 'veterinaria', 'fundacion'])->default('user');
            $table->enum('estado', ['activo', 'inactivo', 'suspendido', 'pendiente'])->default('activo');
            $table->integer('veces_reportado')->default(0);
            $table->integer('total_mascotas_adoptadas')->default(0);
            $table->decimal('total_donaciones', 10, 2)->default(0);
            $table->integer('puntos')->default(0);
            $table->string('rango')->default('Nuevo');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('direccion')->nullable();
            $table->string('pais')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('telefono')->nullable();
            $table->string('avatar')->nullable();
            $table->string('avatar_public_id')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->string('numero_documento')->nullable()->unique();
            $table->boolean('documento_verificado')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->boolean('telefono_verificado')->default(false);
            $table->rememberToken();
            $table->json('preferencias_notificaciones')->nullable();
            $table->string('idioma')->default('es');
            $table->string('tema')->default('light');
            $table->timestamp('ultimo_acceso')->nullable();
            $table->string('ultima_ip')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            // Índices
            $table->index('email');
            $table->index('tipo');
            $table->index('estado');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
