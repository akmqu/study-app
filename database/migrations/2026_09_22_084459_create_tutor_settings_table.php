<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_settings', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('tutor_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table
                ->unsignedSmallInteger('default_lesson_duration')
                ->default(60);

            $table
                ->boolean('lesson_reminders_enabled')
                ->default(true);

            $table
                ->unsignedSmallInteger('lesson_reminder_minutes')
                ->default(60);

            $table
                ->string('default_billing_type')
                ->default('per_lesson');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_settings');
    }
};