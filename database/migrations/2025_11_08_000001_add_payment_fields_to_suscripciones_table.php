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

            // Para integración con pasarelas reales
            $table->string('stripe_subscription_id')->nullable()->after('payment_reference');
            $table->string('paypal_subscription_id')->nullable()->after('stripe_subscription_id');
            $table->string('mercadopago_subscription_id')->nullable()->after('paypal_subscription_id');

            // Índices
            $table->index('es_demo', 'idx_suscripciones_es_demo');
            $table->index('payment_reference', 'idx_suscripciones_payment_reference');
            $table->index('stripe_subscription_id', 'idx_suscripciones_stripe');
        });
    }

    public function down(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropIndex('idx_suscripciones_es_demo');
            $table->dropIndex('idx_suscripciones_payment_reference');
            $table->dropIndex('idx_suscripciones_stripe');

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
