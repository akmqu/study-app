<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table
                ->decimal(
                    'billing_amount',
                    8,
                    2
                )
                ->nullable();

            $table
                ->string(
                    'billing_type'
                )
                ->nullable();

            $table
                ->foreignId(
                    'payment_id'
                )
                ->nullable()
                ->constrained(
                    'payments'
                )
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign([
                'payment_id',
            ]);

            $table->dropColumn([
                'billing_amount',
                'billing_type',
                'payment_id',
            ]);
        });
    }
};