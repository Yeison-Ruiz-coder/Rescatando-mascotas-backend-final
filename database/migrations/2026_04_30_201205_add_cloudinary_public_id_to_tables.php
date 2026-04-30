<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Para mascotas
        Schema::table('mascotas', function (Blueprint $table) {
            $table->string('foto_principal_public_id')->nullable()->after('foto_principal');
        });

        // Para usuarios (avatar)
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_public_id')->nullable()->after('avatar');
        });

        // Para eventos
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('imagen_public_id')->nullable()->after('imagen_url');
        });

        // Para fundaciones
        Schema::table('fundaciones', function (Blueprint $table) {
            $table->string('imagen_portada_public_id')->nullable()->after('imagen_portada');
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropColumn('foto_principal_public_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_public_id');
        });
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('imagen_public_id');
        });
        Schema::table('fundaciones', function (Blueprint $table) {
            $table->dropColumn('imagen_portada_public_id');
        });
    }
};
