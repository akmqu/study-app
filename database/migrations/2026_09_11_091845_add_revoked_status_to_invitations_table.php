<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE invitations
            DROP CONSTRAINT invitations_status_check
        ');

        DB::statement("
            ALTER TABLE invitations
            ADD CONSTRAINT invitations_status_check
            CHECK (status IN ('pending', 'accepted', 'revoked'))
        ");
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE invitations
            DROP CONSTRAINT invitations_status_check
        ');

        DB::statement("
            ALTER TABLE invitations
            ADD CONSTRAINT invitations_status_check
            CHECK (status IN ('pending', 'accepted'))
        ");
    }
};