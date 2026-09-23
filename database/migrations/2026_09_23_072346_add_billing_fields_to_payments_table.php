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
                ->string(
                    'billing_type'
                )
                ->nullable();

            $table
                ->date(
                    'period_start'
                )
                ->nullable();

            $table
                ->date(
                    'period_end'
                )
                ->nullable();

            $table
                ->unsignedInteger(
                    'lesson_count'
                )
                ->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'billing_type',
                'period_start',
                'period_end',
                'lesson_count',
            ]);
        });
    }
};