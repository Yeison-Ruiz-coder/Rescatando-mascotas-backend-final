<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rescates', function (Blueprint $table) {
            $table->enum('tipo_emergencia', ['herido', 'abandonado', 'urgente', 'otro'])->nullable()->after('descripcion_rescate');
            $table->enum('prioridad', ['alta', 'media', 'baja'])->default('media')->after('tipo_emergencia');
            $table->decimal('lat', 10, 8)->nullable()->after('lugar_rescate');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('rescates', function (Blueprint $table) {
            $table->dropColumn(['tipo_emergencia', 'prioridad', 'lat', 'lng']);
        });
    }
};
