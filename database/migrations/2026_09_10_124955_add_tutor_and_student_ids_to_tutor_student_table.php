<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tutor_student', function (Blueprint $table) {
            $table->foreignId('tutor_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->after('tutor_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['tutor_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_student', function (Blueprint $table) {
            $table->dropUnique(['tutor_id', 'student_id']);
            $table->dropConstrainedForeignId('tutor_id');
            $table->dropConstrainedForeignId('student_id');
        });
    }
};
