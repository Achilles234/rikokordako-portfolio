
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('scope', 20);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            // Composite key for role scope enforcement.
            $table->unique(
                ['id', 'scope'],
                'roles_id_scope_unique'
            );

            $table->index('scope');
        });

        DB::table('roles')->insert([
    [
        'name' => 'Super Admin',
        'slug' => 'super_admin',
        'scope' => 'global',
        'description' => 'Administrator global Family Portal.',
        'is_system' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Family Admin',
        'slug' => 'family_admin',
        'scope' => 'group',
        'description' => 'Administrator untuk grup keluarga yang ditugaskan.',
        'is_system' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Anggota Keluarga',
        'slug' => 'family_member',
        'scope' => 'group',
        'description' => 'Anggota biasa dalam grup keluarga.',
        'is_system' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
        /*
        |--------------------------------------------------------------------------
        | Family Portal Settings
        |--------------------------------------------------------------------------
        */

        Schema::create('family_portal_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();

            $table->foreignId('super_admin_user_id')
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Family Groups
        |--------------------------------------------------------------------------
        */

        Schema::create('family_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group_type', 30)->default('custom');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('archived_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'group_type']);
        });

        /*
        |--------------------------------------------------------------------------
        | Family Persons
        |--------------------------------------------------------------------------
        */

        Schema::create('family_persons', function (Blueprint $table) {
            $table->id();

            // Login account is optional.
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('full_name');
            $table->string('nickname')->nullable();
            $table->string('sex', 20)->nullable();

            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();

            $table->date('death_date')->nullable();
            $table->string('death_place')->nullable();

            $table->string('life_status', 20)->default('unknown');

            $table->string('photo_path')->nullable();
            $table->text('biography')->nullable();

            $table->timestamps();

            $table->index('full_name');
            $table->index('life_status');
        });

        /*
        |--------------------------------------------------------------------------
        | Family Members
        |--------------------------------------------------------------------------
        */

        Schema::create('family_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_group_id')
                ->constrained('family_groups')
                ->restrictOnDelete();

            $table->foreignId('family_person_id')
                ->constrained('family_persons')
                ->restrictOnDelete();

            // Group-scoped role only.
            $table->foreignId('role_id');

            $table->string('role_scope', 20)->default('group');

            $table->foreign(
                ['role_id', 'role_scope'],
                'family_members_role_scope_fk'
            )
                ->references(['id', 'scope'])
                ->on('roles')
                ->restrictOnDelete();

            $table->string('status', 20)->default('pending');

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['family_group_id', 'family_person_id'],
                'family_members_group_person_unique'
            );

            $table->index(['family_group_id', 'status']);
        });

        /*
        |--------------------------------------------------------------------------
        | Family Relationships
        |--------------------------------------------------------------------------
        */

        Schema::create('family_relationships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('person_id')
                ->constrained('family_persons')
                ->restrictOnDelete();

            $table->foreignId('related_person_id')
                ->constrained('family_persons')
                ->restrictOnDelete();

            // parent, spouse, guardian
            $table->string('relationship_type', 30);

            // biological, adoptive, step (parent only)
            // legal, informal (guardian only)
            $table->string('relationship_subtype', 30)->nullable();

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['person_id', 'relationship_type'],
                'family_relationships_person_type_index'
            );

            $table->index(
                ['related_person_id', 'relationship_type'],
                'family_relationships_related_type_index'
            );

            $table->index(
                ['relationship_type', 'relationship_subtype'],
                'family_relationships_type_subtype_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Role Permissions
        |--------------------------------------------------------------------------
        */

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['role_id', 'permission_id'],
                'role_permissions_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Global User Roles
        |--------------------------------------------------------------------------
        */

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Global-scoped role only.
            $table->foreignId('role_id');

            $table->string('role_scope', 20)->default('global');

            $table->foreign(
                ['role_id', 'role_scope'],
                'user_roles_role_scope_fk'
            )
                ->references(['id', 'scope'])
                ->on('roles')
                ->restrictOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['user_id', 'role_id'],
                'user_roles_user_role_unique'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | MySQL Database Constraints
        |--------------------------------------------------------------------------
        */

        if (DB::getDriverName() === 'mysql') {

            // Only singleton settings record with ID 1.
            DB::statement("
                ALTER TABLE family_portal_settings
                ADD CONSTRAINT family_portal_settings_singleton
                CHECK (id = 1)
            ");

            // Family members can only use group-scoped roles.
            DB::statement("
                ALTER TABLE family_members
                ADD CONSTRAINT family_members_role_scope_check
                CHECK (role_scope = 'group')
            ");

            // User roles can only use global-scoped roles.
            DB::statement("
                ALTER TABLE user_roles
                ADD CONSTRAINT user_roles_role_scope_check
                CHECK (role_scope = 'global')
            ");

            // A person cannot have a relationship with themselves.
            DB::statement("
                ALTER TABLE family_relationships
                ADD CONSTRAINT family_relationships_no_self
                CHECK (person_id <> related_person_id)
            ");

            // End date cannot be earlier than start date.
            DB::statement("
                ALTER TABLE family_relationships
                ADD CONSTRAINT family_relationships_valid_dates
                CHECK (
                    started_at IS NULL
                    OR ended_at IS NULL
                    OR ended_at >= started_at
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('family_relationships');
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('family_persons');
        Schema::dropIfExists('family_groups');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('family_portal_settings');
        Schema::dropIfExists('roles');
    }
};
