<?php

namespace Tests\Feature\Family;

use App\Models\Family\FamilyGroup;
use App\Models\User;
use App\Services\Family\FamilyGroupService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FamilyGroupServiceTest extends TestCase
{
    use RefreshDatabase;

    private FamilyGroupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FamilyGroupService::class);
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'test-password',
        ]);
    }

   private function createRole(string $name, string $slug, string $scope): int
{
    $role = DB::table('roles')
        ->where('slug', $slug)
        ->where('scope', $scope)
        ->first();

    if ($role) {
        return (int) $role->id;
    }

    return DB::table('roles')->insertGetId([
        'name' => $name,
        'slug' => $slug,
        'scope' => $scope,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

    private function grantPermissions(int $roleId, array $slugs): void
    {
        foreach ($slugs as $slug) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $slug,
                'slug' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createSuperAdmin(array $permissions): User
    {
        $user = $this->createUser('super-admin@example.test');

        $roleId = $this->createRole(
            'Super Admin',
            'super_admin',
            'global'
        );

        $this->grantPermissions($roleId, $permissions);

        DB::table('user_roles')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'role_scope' => 'global',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('family_portal_settings')->insert([
            'id' => 1,
            'super_admin_user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function createGroupAdmin(
        string $email,
        int $groupId,
        array $permissions
    ): User {
        $user = $this->createUser($email);

        $personId = DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => 'Family Admin',
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = $this->createRole(
            'Family Admin',
            'family_admin',
            'group'
        );

        $this->grantPermissions($roleId, $permissions);

        DB::table('family_members')->insert([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => 'active',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function createGroup(
        string $name,
        string $slug,
        string $status = 'active'
    ): FamilyGroup {
        return FamilyGroup::create([
            'name' => $name,
            'slug' => $slug,
            'group_type' => 'custom',
            'status' => $status,
            'archived_at' => $status === 'archived' ? now() : null,
        ]);
    }

    public function test_super_admin_can_create_group_and_records_audit_log(): void
    {
        $admin = $this->createSuperAdmin(['groups.create']);

        $group = $this->service->create($admin, [
            'name' => 'Keluarga Besar Kordako',
            'group_type' => 'extended',
            'description' => 'Grup keluarga besar.',
        ]);

        $this->assertSame('keluarga-besar-kordako', $group->slug);
        $this->assertSame('active', $group->status);

        $this->assertDatabaseHas('family_groups', [
            'id' => $group->id,
            'name' => 'Keluarga Besar Kordako',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('family_group_activity_logs', [
            'family_group_id' => $group->id,
            'action' => 'created',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_duplicate_group_names_generate_unique_slugs(): void
    {
        $admin = $this->createSuperAdmin(['groups.create']);

        $first = $this->service->create($admin, [
            'name' => 'Keluarga Kordako',
        ]);

        $second = $this->service->create($admin, [
            'name' => 'Keluarga Kordako',
        ]);

        $this->assertSame('keluarga-kordako', $first->slug);
        $this->assertSame('keluarga-kordako-1', $second->slug);
    }

    public function test_group_admin_can_update_own_group_details_without_changing_slug(): void
    {
        $group = $this->createGroup('Old Name', 'old-name');

        $admin = $this->createGroupAdmin(
            'group-admin@example.test',
            $group->id,
            ['groups.update']
        );

        $updated = $this->service->updateDetails(
            $admin,
            $group->id,
            [
                'name' => 'New Family Name',
                'description' => 'Updated description',
            ]
        );

        $this->assertSame('New Family Name', $updated->name);
        $this->assertSame('old-name', $updated->slug);
        $this->assertSame('Updated description', $updated->description);

        $this->assertDatabaseHas('family_group_activity_logs', [
            'family_group_id' => $group->id,
            'action' => 'details_updated',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_group_admin_cannot_update_another_group(): void
    {
        $groupA = $this->createGroup('Group A', 'group-a');
        $groupB = $this->createGroup('Group B', 'group-b');

        $admin = $this->createGroupAdmin(
            'group-a-admin@example.test',
            $groupA->id,
            ['groups.update']
        );

        try {
            $this->service->updateDetails(
                $admin,
                $groupB->id,
                ['name' => 'Unauthorized Change']
            );

            $this->fail('Expected authorization exception was not thrown.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('family_groups', [
                'id' => $groupB->id,
                'name' => 'Group B',
            ]);
        }
    }

    public function test_only_super_admin_can_change_group_type(): void
    {
        $group = $this->createGroup('Type Group', 'type-group');

        $admin = $this->createGroupAdmin(
            'type-admin@example.test',
            $group->id,
            ['groups.update']
        );

        $this->expectException(AuthorizationException::class);

        $this->service->changeType(
            $admin,
            $group->id,
            'nuclear'
        );
    }

    public function test_super_admin_can_change_group_type_and_records_log(): void
    {
        $admin = $this->createSuperAdmin(['groups.type.manage']);
        $group = $this->createGroup('Type Group', 'type-group');

        $updated = $this->service->changeType(
            $admin,
            $group->id,
            'nuclear'
        );

        $this->assertSame('nuclear', $updated->group_type);

        $this->assertDatabaseHas('family_group_activity_logs', [
            'family_group_id' => $group->id,
            'action' => 'type_changed',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_super_admin_can_deactivate_and_activate_group(): void
    {
        $admin = $this->createSuperAdmin(['groups.activate']);
        $group = $this->createGroup('Status Group', 'status-group');

        $inactive = $this->service->changeStatus(
            $admin,
            $group->id,
            'inactive'
        );

        $this->assertSame('inactive', $inactive->status);

        $active = $this->service->changeStatus(
            $admin,
            $group->id,
            'active'
        );

        $this->assertSame('active', $active->status);

        $this->assertDatabaseHas('family_group_activity_logs', [
            'family_group_id' => $group->id,
            'action' => 'deactivated',
        ]);

        $this->assertDatabaseHas('family_group_activity_logs', [
            'family_group_id' => $group->id,
            'action' => 'activated',
        ]);
    }

    public function test_super_admin_can_archive_and_restore_group(): void
    {
        $admin = $this->createSuperAdmin([
            'groups.archive',
            'groups.restore',
        ]);

        $group = $this->createGroup('Archive Group', 'archive-group');

        $archived = $this->service->archive(
            $admin,
            $group->id,
            'No longer used'
        );

        $this->assertSame('archived', $archived->status);
        $this->assertNotNull($archived->archived_at);

        $this->assertDatabaseHas('family_group_activity_logs', [
            'family_group_id' => $group->id,
            'action' => 'archived',
            'reason' => 'No longer used',
        ]);

        $restored = $this->service->restore(
            $admin,
            $group->id,
            'Group is active again'
        );

        $this->assertSame('active', $restored->status);
        $this->assertNull($restored->archived_at);

        $this->assertDatabaseHas('family_group_activity_logs', [
            'family_group_id' => $group->id,
            'action' => 'restored',
            'reason' => 'Group is active again',
        ]);
    }

    public function test_group_admin_cannot_archive_or_change_status(): void
    {
        $group = $this->createGroup('Protected Group', 'protected-group');

        $admin = $this->createGroupAdmin(
            'protected-admin@example.test',
            $group->id,
            ['groups.update']
        );

        try {
            $this->service->archive($admin, $group->id);
            $this->fail('Expected authorization exception was not thrown.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('family_groups', [
                'id' => $group->id,
                'status' => 'active',
            ]);
        }

        try {
            $this->service->changeStatus(
                $admin,
                $group->id,
                'inactive'
            );

            $this->fail('Expected authorization exception was not thrown.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('family_groups', [
                'id' => $group->id,
                'status' => 'active',
            ]);
        }
    }

    public function test_archived_group_cannot_be_edited(): void
    {
        $admin = $this->createSuperAdmin(['groups.update']);
        $group = $this->createGroup(
            'Archived Group',
            'archived-group',
            'archived'
        );

        $this->expectException(ValidationException::class);

        $this->service->updateDetails(
            $admin,
            $group->id,
            ['name' => 'Changed Name']
        );
    }


public function test_group_change_rolls_back_when_audit_log_creation_fails(): void
{
    $admin = $this->createSuperAdmin(['groups.activate']);

    $group = $this->createGroup(
        'Rollback Group',
        'rollback-group'
    );

    DB::listen(function ($query) {
        $sql = strtolower(trim($query->sql));

        if (
            str_starts_with($sql, 'insert')
            && str_contains($sql, 'family_group_activity_logs')
        ) {
            throw new \RuntimeException('Simulated audit log failure.');
        }
    });

    try {
        $this->service->changeStatus(
            $admin,
            $group->id,
            'inactive'
        );

        $this->fail('Expected audit log failure was not thrown.');
    } catch (\RuntimeException $exception) {
        $this->assertSame(
            'Simulated audit log failure.',
            $exception->getMessage()
        );
    }

    $this->assertDatabaseHas('family_groups', [
        'id' => $group->id,
        'status' => 'active',
    ]);

    $this->assertDatabaseMissing('family_group_activity_logs', [
        'family_group_id' => $group->id,
        'action' => 'deactivated',
    ]);
}


public function test_group_admin_cannot_change_type_or_restore_group(): void
{
    $group = $this->createGroup(
        'Protected Group',
        'protected-group'
    );

    $admin = $this->createGroupAdmin(
        'protected-admin-extra@example.test',
        $group->id,
        ['groups.update']
    );

    // Family Admin tidak boleh mengubah tipe.
    try {
        $this->service->changeType(
            $admin,
            $group->id,
            'extended'
        );

        $this->fail('Expected authorization exception was not thrown.');
    } catch (AuthorizationException) {
        $this->assertDatabaseHas('family_groups', [
            'id' => $group->id,
            'group_type' => 'custom',
            'status' => 'active',
        ]);
    }

    // Buat grup arsip untuk pengujian restore.
    $archivedGroup = $this->createGroup(
        'Archived Protected Group',
        'archived-protected-group',
        'archived'
    );

    // Family Admin tidak boleh memulihkan grup.
    try {
        $this->service->restore(
            $admin,
            $archivedGroup->id
        );

        $this->fail('Expected authorization exception was not thrown.');
    } catch (AuthorizationException) {
        $this->assertDatabaseHas('family_groups', [
            'id' => $archivedGroup->id,
            'status' => 'archived',
        ]);
    }

    // Penolakan tindakan tidak boleh menghasilkan audit log.
    $this->assertDatabaseMissing('family_group_activity_logs', [
        'family_group_id' => $group->id,
        'action' => 'type_changed',
    ]);

    $this->assertDatabaseMissing('family_group_activity_logs', [
        'family_group_id' => $archivedGroup->id,
        'action' => 'restored',
    ]);
}

}
