
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
            ALTER TABLE family_relationships
            DROP CHECK family_relationships_valid_type_subtype
        ");

        DB::statement("
            ALTER TABLE family_relationships
            ADD CONSTRAINT family_relationships_valid_type_subtype
            CHECK (
                (
                    relationship_type = 'parent'
                    AND relationship_subtype IS NOT NULL
                    AND relationship_subtype IN ('biological', 'adoptive', 'step')
                )
                OR (
                    relationship_type = 'guardian'
                    AND relationship_subtype IS NOT NULL
                    AND relationship_subtype IN ('legal', 'informal')
                )
                OR (
                    relationship_type = 'spouse'
                    AND relationship_subtype IS NULL
                )
            )
        ");

        DB::statement("
            ALTER TABLE family_relationships
            DROP CHECK family_relationships_valid_parent_role
        ");

        DB::statement("
            ALTER TABLE family_relationships
            ADD CONSTRAINT family_relationships_valid_parent_role
            CHECK (
                (
                    relationship_type = 'parent'
                    AND parent_role IS NOT NULL
                    AND parent_role IN ('father', 'mother', 'unspecified')
                )
                OR (
                    relationship_type <> 'parent'
                    AND parent_role IS NULL
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
            ALTER TABLE family_relationships
            DROP CHECK family_relationships_valid_parent_role
        ");

        DB::statement("
            ALTER TABLE family_relationships
            ADD CONSTRAINT family_relationships_valid_parent_role
            CHECK (
                (
                    relationship_type = 'parent'
                    AND parent_role IN ('father', 'mother', 'unspecified')
                )
                OR (
                    relationship_type <> 'parent'
                    AND parent_role IS NULL
                )
            )
        ");

        DB::statement("
            ALTER TABLE family_relationships
            DROP CHECK family_relationships_valid_type_subtype
        ");

        DB::statement("
            ALTER TABLE family_relationships
            ADD CONSTRAINT family_relationships_valid_type_subtype
            CHECK (
                (
                    relationship_type = 'parent'
                    AND relationship_subtype IN ('biological', 'adoptive', 'step')
                )
                OR (
                    relationship_type = 'guardian'
                    AND relationship_subtype IN ('legal', 'informal')
                )
                OR (
                    relationship_type = 'spouse'
                    AND relationship_subtype IS NULL
                )
            )
        ");
    }
};
