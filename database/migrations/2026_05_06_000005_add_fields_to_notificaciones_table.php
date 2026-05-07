<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('notificaciones', 'titulo')) {
                $table->string('titulo')->nullable()->after('id');
            }

            if (!Schema::hasColumn('notificaciones', 'tipo')) {
                $table->enum('tipo', ['info', 'success', 'warning', 'error', 'alert'])->default('info')->after('contenido');
            }

            if (!Schema::hasColumn('notificaciones', 'icono')) {
                $table->string('icono')->nullable()->after('tipo');
            }

            if (!Schema::hasColumn('notificaciones', 'color')) {
                $table->string('color')->nullable()->after('icono');
            }

            if (!Schema::hasColumn('notificaciones', 'url_accion')) {
                $table->string('url_accion')->nullable()->after('color');
            }

            if (!Schema::hasColumn('notificaciones', 'texto_accion')) {
                $table->string('texto_accion')->nullable()->after('url_accion');
            }

            if (!Schema::hasColumn('notificaciones', 'metadata')) {
                $table->json('metadata')->nullable()->after('texto_accion');
            }

            if (!Schema::hasColumn('notificaciones', 'prioridad')) {
                $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media')->after('metadata');
            }

            if (!Schema::hasColumn('notificaciones', 'leida')) {
                $table->boolean('leida')->default(false)->after('fecha_envio');
            }

            if (!Schema::hasColumn('notificaciones', 'leida_en')) {
                $table->timestamp('leida_en')->nullable()->after('leida');
            }

            if (!Schema::hasColumn('notificaciones', 'expira_en')) {
                $table->timestamp('expira_en')->nullable()->after('leida_en');
            }

            if (!Schema::hasColumn('notificaciones', 'enviada_email')) {
                $table->boolean('enviada_email')->default(false)->after('expira_en');
            }

            if (!Schema::hasColumn('notificaciones', 'enviada_push')) {
                $table->boolean('enviada_push')->default(false)->after('enviada_email');
            }

            $table->index(['user_id', 'leida', 'created_at']);
            $table->index(['user_id', 'prioridad', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $columns = [
                'titulo', 'tipo', 'icono', 'color', 'url_accion', 'texto_accion',
                'metadata', 'prioridad', 'leida', 'leida_en', 'expira_en',
                'enviada_email', 'enviada_push'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('notificaciones', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
