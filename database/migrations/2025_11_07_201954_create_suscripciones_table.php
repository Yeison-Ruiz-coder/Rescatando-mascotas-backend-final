<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            // Campos para el sistema de pagos
            $table->boolean('es_demo')->default(true)->after('estado');
            $table->string('payment_method')->nullable()->after('es_demo');
            $table->string('payment_reference')->nullable()->after('payment_method');

            // Para cuando integres Stripe/PayPal real
            $table->string('stripe_subscription_id')->nullable()->after('payment_reference');
            $table->string('paypal_subscription_id')->nullable()->after('stripe_subscription_id');
            $table->string('mercadopago_subscription_id')->nullable()->after('paypal_subscription_id');

            // Índices para búsquedas
            $table->index('es_demo');
            $table->index('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropColumn([
                'es_demo',
                'payment_method',
                'payment_reference',
                'stripe_subscription_id',
                'paypal_subscription_id',
                'mercadopago_subscription_id',
            ]);
        });
    }
};
