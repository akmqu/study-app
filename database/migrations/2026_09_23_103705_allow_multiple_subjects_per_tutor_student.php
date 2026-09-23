<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutor_student', function (Blueprint $table) {
            $table->dropUnique('tutor_student_tutor_id_student_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tutor_student', function (Blueprint $table) {
            $table->unique(['tutor_id', 'student_id']);
        });
    }
};