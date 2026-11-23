
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE family_person_privacy_settings
                ADD CONSTRAINT family_person_privacy_settings_valid_visibility
                CHECK (
                    default_field_visibility IN (
                        'group_members',
                        'admins_only',
                        'hidden'
                    )
                )
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE family_person_privacy_settings
                DROP CHECK family_person_privacy_settings_valid_visibility
            ");
        }
    }
};
