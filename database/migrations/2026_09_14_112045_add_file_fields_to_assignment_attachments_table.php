<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('assignment_attachments', 'file_path')) {
                $table->string('file_path')->nullable();
            }

            if (! Schema::hasColumn('assignment_attachments', 'original_name')) {
                $table->string('original_name')->nullable();
            }

            if (! Schema::hasColumn('assignment_attachments', 'mime_type')) {
                $table->string('mime_type')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignment_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('assignment_attachments', 'mime_type')) {
                $table->dropColumn('mime_type');
            }

            if (Schema::hasColumn('assignment_attachments', 'original_name')) {
                $table->dropColumn('original_name');
            }

            if (Schema::hasColumn('assignment_attachments', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
};