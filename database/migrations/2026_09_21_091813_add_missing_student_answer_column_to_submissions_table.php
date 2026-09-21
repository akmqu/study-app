<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasColumn(
                'submissions',
                'student_answer'
            )
        ) {
            Schema::table(
                'submissions',
                function (Blueprint $table) {
                    $table
                        ->text('student_answer')
                        ->nullable();
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'submissions',
                'student_answer'
            )
        ) {
            Schema::table(
                'submissions',
                function (Blueprint $table) {
                    $table->dropColumn(
                        'student_answer'
                    );
                }
            );
        }
    }
};