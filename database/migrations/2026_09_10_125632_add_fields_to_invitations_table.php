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
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreignId('tutor_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 8)->unique()->after('tutor_id');
            $table->string('status')->default('pending')->after('code');
            $table->foreignId('student_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tutor_id');
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'status']);
            $table->dropConstrainedForeignId('student_id');
        });
    }
};
