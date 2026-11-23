
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE family_members
            ADD CONSTRAINT family_members_valid_status
            CHECK (
                status IN (
                    'pending',
                    'active',
                    'inactive',
                    'left',
                    'rejected'
                )
            )
        ");

        DB::statement("
            ALTER TABLE family_members
            ADD CONSTRAINT family_members_valid_membership_dates
            CHECK (
                (
                    status IN ('active', 'inactive')
                    AND joined_at IS NOT NULL
                    AND left_at IS NULL
                )
                OR
                (
                    status = 'left'
                    AND joined_at IS NOT NULL
                    AND left_at IS NOT NULL
                    AND left_at >= joined_at
                )
                OR
                (
                    status IN ('pending', 'rejected')
                    AND joined_at IS NULL
                    AND left_at IS NULL
                )
            )
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE family_members
            DROP CHECK family_members_valid_membership_dates
        ");

        DB::statement("
            ALTER TABLE family_members
            DROP CHECK family_members_valid_status
        ");
    }
};
