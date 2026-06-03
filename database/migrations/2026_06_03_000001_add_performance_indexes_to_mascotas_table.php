<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->index('estado');
            $table->index('fundacion_id');
            $table->index('especie');
            $table->index('destacada');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['fundacion_id']);
            $table->dropIndex(['especie']);
            $table->dropIndex(['destacada']);
            $table->dropIndex(['created_at']);
        });
    }
};
