
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_person_field_visibility', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('family_group_id');
            $table->unsignedBigInteger('family_person_id');

            $table->string('field_key', 50);
            $table->string('visibility', 20);

            $table->timestamps();

            $table->unique(
                ['family_group_id', 'family_person_id', 'field_key'],
                'family_group_person_field_unique'
            );

            $table->foreign(
                ['family_group_id', 'family_person_id'],
                'family_person_field_visibility_membership_fk'
            )
                ->references(['family_group_id', 'family_person_id'])
                ->on('family_members')
                ->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
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

            DB::statement("
                ALTER TABLE family_person_field_visibility
                ADD CONSTRAINT family_person_field_visibility_valid_visibility
                CHECK (visibility IN (
                    'group_members',
                    'admins_only',
                    'hidden'
                ))
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('family_person_field_visibility');
    }
};
