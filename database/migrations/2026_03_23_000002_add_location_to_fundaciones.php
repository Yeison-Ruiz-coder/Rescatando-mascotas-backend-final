<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fundaciones', function (Blueprint $table) {
            $table->decimal('lat', 10, 8)->nullable()->after('Direccion');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');
            $table->integer('radio_atencion')->default(10)->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('fundaciones', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'radio_atencion']);
        });
    }
};
