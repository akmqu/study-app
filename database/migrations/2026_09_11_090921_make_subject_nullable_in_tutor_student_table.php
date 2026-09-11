<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tutor_student ALTER COLUMN subject DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tutor_student ALTER COLUMN subject SET NOT NULL');
    }
};