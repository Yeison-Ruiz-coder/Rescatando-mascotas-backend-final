<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreignId('veterinaria_id')->nullable()->constrained('veterinarias')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE eventos MODIFY tipo ENUM('fundacion','veterinaria','admin') NOT NULL DEFAULT 'fundacion';");
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['veterinaria_id']);
            $table->dropColumn('veterinaria_id');
        });

        DB::statement("ALTER TABLE eventos MODIFY tipo ENUM('fundacion','admin') NOT NULL DEFAULT 'fundacion';");
    }
};
