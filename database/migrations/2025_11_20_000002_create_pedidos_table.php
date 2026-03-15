<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();

            // 👇 Quién compra (puede ser cualquier user)
            $table->foreignId('comprador_id')->constrained('users')->onDelete('cascade');

            // 👇 Quién vende (fundación o veterinaria)
            $table->foreignId('vendedor_id')->constrained('users')->onDelete('cascade');

            // Totales
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);

            // Estado
            $table->enum('estado', ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'])->default('pendiente');

            // Información de envío
            $table->string('direccion_envio');
            $table->string('telefono_contacto');
            $table->text('notas')->nullable();

            $table->timestamps();

            $table->index('codigo');
            $table->index('comprador_id');
            $table->index('vendedor_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
