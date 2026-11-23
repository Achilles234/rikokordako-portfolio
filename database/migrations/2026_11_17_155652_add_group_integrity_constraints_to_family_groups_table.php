
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
            ALTER TABLE family_groups
            ADD CONSTRAINT family_groups_valid_status
            CHECK (
                status IN ('active', 'inactive', 'archived')
            )
        ");

        DB::statement("
            ALTER TABLE family_groups
            ADD CONSTRAINT family_groups_archive_consistency
            CHECK (
                (
                    status IN ('active', 'inactive')
                    AND archived_at IS NULL
                )
                OR
                (
                    status = 'archived'
                    AND archived_at IS NOT NULL
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
            ALTER TABLE family_groups
            DROP CHECK family_groups_archive_consistency
        ");

        DB::statement("
            ALTER TABLE family_groups
            DROP CHECK family_groups_valid_status
        ");
    }
};
