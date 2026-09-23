<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table
                ->string('currency', 3)
                ->default('pln')
                ->after('amount');

            $table
                ->string('stripe_checkout_session_id')
                ->nullable()
                ->unique()
                ->after('payment_method');

            $table
                ->string('stripe_payment_intent_id')
                ->nullable()
                ->unique()
                ->after('stripe_checkout_session_id');

            $table
                ->timestamp('paid_at')
                ->nullable()
                ->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique([
                'stripe_checkout_session_id',
            ]);

            $table->dropUnique([
                'stripe_payment_intent_id',
            ]);

            $table->dropColumn([
                'currency',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'paid_at',
            ]);
        });
    }
};