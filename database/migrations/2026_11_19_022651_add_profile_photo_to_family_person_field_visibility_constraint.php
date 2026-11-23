
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE family_person_field_visibility
                DROP CHECK family_person_field_visibility_valid_field
            ");

            DB::statement("
                ALTER TABLE family_person_field_visibility
                ADD CONSTRAINT family_person_field_visibility_valid_field
                CHECK (field_key IN (
                    'home_address',
                    'phone',
                    'email',
                    'occupation',
                    'education',
                    'hobbies',
                    'profile_photo'
                ))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE family_person_field_visibility
                DROP CHECK family_person_field_visibility_valid_field
            ");

            DB::statement("
                ALTER TABLE family_person_field_visibility
                ADD CONSTRAINT family_person_field_visibility_valid_field
                CHECK (field_key IN (
                    'home_address',
                    'phone',
                    'email',
                    'occupation',
                    'education',
                    'hobbies'
                ))
            ");
        }
    }
};
