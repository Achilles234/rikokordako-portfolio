
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_relationships', function (Blueprint $table) {
            $table->string('parent_role', 20)
                ->nullable()
                ->after('relationship_subtype');

            // Parent and guardian identity includes subtype.
            // Multiple spouse history rows remain possible because
            // relationship_subtype is NULL for spouse.
            $table->unique(
                [
                    'person_id',
                    'related_person_id',
                    'relationship_type',
                    'relationship_subtype',
                ],
                'family_relationships_identity_unique'
            );
        });

        // Existing parent records, if any, receive a neutral role.
        DB::table('family_relationships')
            ->where('relationship_type', 'parent')
            ->whereNull('parent_role')
            ->update(['parent_role' => 'unspecified']);

        // Database CHECK constraints are added on MySQL.
        // SQLite test coverage is provided by service validation.
        if (DB::getDriverName() === 'mysql') {
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
                ADD CONSTRAINT family_relationships_canonical_spouse
                CHECK (
                    relationship_type <> 'spouse'
                    OR person_id < related_person_id
                )
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE family_relationships
                DROP CHECK family_relationships_canonical_spouse
            ");

            DB::statement("
                ALTER TABLE family_relationships
                DROP CHECK family_relationships_valid_parent_role
            ");

            DB::statement("
                ALTER TABLE family_relationships
                DROP CHECK family_relationships_valid_type_subtype
            ");
        }

        Schema::table('family_relationships', function (Blueprint $table) {
            $table->dropUnique('family_relationships_identity_unique');
            $table->dropColumn('parent_role');
        });
    }
};
