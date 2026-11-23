<?php

namespace Tests\Feature\Family;

use App\Models\User;
use App\Services\Family\FamilyAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Gate;

class FamilyAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    private FamilyAccessService $accessService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accessService = app(FamilyAccessService::class);
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'test-password',
        ]);
    }

    private function createRole(
    string $name,
    string $slug,
    string $scope
): int {
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

    public function test_designated_user_with_global_super_admin_role_is_super_admin(): void
    {
        $user = $this->createUser('superadmin@example.test');

        $roleId = $this->createRole(
            'Super Admin',
            'super_admin',
            'global'
        );

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

        $this->assertTrue(
            $this->accessService->isSuperAdmin($user)
        );

        $this->assertTrue(
            $this->accessService->canAccess($user)
        );
    }

    public function test_user_with_super_admin_role_but_not_designated_is_not_super_admin(): void
    {
        $designatedUser = $this->createUser('designated@example.test');
        $otherUser = $this->createUser('other@example.test');

        $roleId = $this->createRole(
            'Super Admin',
            'super_admin',
            'global'
        );

        DB::table('user_roles')->insert([
            'user_id' => $otherUser->id,
            'role_id' => $roleId,
            'role_scope' => 'global',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('family_portal_settings')->insert([
            'id' => 1,
            'super_admin_user_id' => $designatedUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(
            $this->accessService->isSuperAdmin($otherUser)
        );

        $this->assertFalse(
            $this->accessService->canAccess($otherUser)
        );
    }

    public function test_designated_user_without_super_admin_role_is_not_super_admin(): void
    {
        $user = $this->createUser('missing-role@example.test');

        DB::table('family_portal_settings')->insert([
            'id' => 1,
            'super_admin_user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(
            $this->accessService->isSuperAdmin($user)
        );

        $this->assertFalse(
            $this->accessService->canAccess($user)
        );
    }

    public function test_active_family_member_can_access_family_portal(): void
    {
        $user = $this->createUser('member@example.test');

        $groupId = DB::table('family_groups')->insertGetId([
            'name' => 'Test Family',
            'slug' => 'test-family',
            'group_type' => 'nuclear',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personId = DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => 'Test Member',
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = $this->createRole(
            'Family Member',
            'family_member',
            'group'
        );

        DB::table('family_members')->insert([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(
            $this->accessService->isActiveFamilyMember($user)
        );

        $this->assertTrue(
            $this->accessService->canAccess($user)
        );
    }


    public function test_pending_family_member_cannot_access_family_portal(): void
    {
        $user = $this->createUser('pending@example.test');

        $groupId = DB::table('family_groups')->insertGetId([
            'name' => 'Pending Family',
            'slug' => 'pending-family',
            'group_type' => 'custom',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personId = DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => 'Pending Member',
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = $this->createRole(
            'Family Member',
            'family_member',
            'group'
        );

        DB::table('family_members')->insert([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(
            $this->accessService->isActiveFamilyMember($user)
        );

        $this->assertFalse(
            $this->accessService->canAccess($user)
        );
    }

    public function test_inactive_family_member_cannot_access_family_portal(): void
    {
        $user = $this->createUser('inactive@example.test');

        $groupId = DB::table('family_groups')->insertGetId([
            'name' => 'Inactive Member Family',
            'slug' => 'inactive-member-family',
            'group_type' => 'custom',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personId = DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => 'Inactive Member',
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = $this->createRole(
            'Family Member',
            'family_member',
            'group'
        );

        DB::table('family_members')->insert([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(
            $this->accessService->isActiveFamilyMember($user)
        );

        $this->assertFalse(
            $this->accessService->canAccess($user)
        );
    }

    public function test_member_of_inactive_family_group_cannot_access_family_portal(): void
    {
        $user = $this->createUser('inactive-group@example.test');

        $groupId = DB::table('family_groups')->insertGetId([
            'name' => 'Archived Family',
            'slug' => 'archived-family',
            'group_type' => 'custom',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personId = DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => 'Archived Group Member',
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = $this->createRole(
            'Family Member',
            'family_member',
            'group'
        );

        DB::table('family_members')->insert([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(
            $this->accessService->isActiveFamilyMember($user)
        );

        $this->assertFalse(
            $this->accessService->canAccess($user)
        );
    }


    public function test_member_can_access_if_at_least_one_of_multiple_groups_is_active(): void
    {
        $user = $this->createUser('multi-group@example.test');

        $inactiveGroupId = DB::table('family_groups')->insertGetId([
            'name' => 'Inactive Family',
            'slug' => 'inactive-family',
            'group_type' => 'custom',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $activeGroupId = DB::table('family_groups')->insertGetId([
            'name' => 'Active Family',
            'slug' => 'active-family',
            'group_type' => 'custom',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personId = DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => 'Multi Group Member',
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = $this->createRole(
            'Family Member',
            'family_member',
            'group'
        );

        DB::table('family_members')->insert([
            [
                'family_group_id' => $inactiveGroupId,
                'family_person_id' => $personId,
                'role_id' => $roleId,
                'role_scope' => 'group',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'family_group_id' => $activeGroupId,
                'family_person_id' => $personId,
                'role_id' => $roleId,
                'role_scope' => 'group',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertTrue(
            $this->accessService->isActiveFamilyMember($user)
        );

        $this->assertTrue(
            $this->accessService->canAccess($user)
        );
    }


    private function createGroup(string $name, string $slug, string $status = 'active'): int
    {
        return DB::table('family_groups')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'group_type' => 'custom',
            'status' => $status,
            'archived_at' => $status === 'archived' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPerson(User $user, string $name): int
    {
        return DB::table('family_persons')->insertGetId([
            'user_id' => $user->id,
            'full_name' => $name,
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPermission(string $slug): int
    {
        return DB::table('permissions')->insertGetId([
            'name' => $slug,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function grantPermission(int $roleId, int $permissionId): void
    {
        DB::table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMembership(
        int $groupId,
        int $personId,
        int $roleId,
        string $status = 'active'
    ): void {
        DB::table('family_members')->insert([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => $status,
            'joined_at' => in_array($status, ['active', 'inactive'], true) ? now() : null,
            'left_at' => $status === 'left' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function designateSuperAdmin(User $user, int $roleId): void
    {
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
    }

    public function test_family_admin_permission_is_limited_to_its_own_group(): void
    {
        $user = $this->createUser('admin-multiple-groups@example.test');

        $groupA = $this->createGroup('Group A', 'group-a');
        $groupB = $this->createGroup('Group B', 'group-b');

        $personId = $this->createPerson($user, 'Admin Member');

        $adminRoleId = $this->createRole('Family Admin', 'family_admin', 'group');
        $memberRoleId = $this->createRole('Family Member', 'family_member', 'group');

        $permissionId = $this->createPermission('members.manage');

        $this->grantPermission($adminRoleId, $permissionId);

        $this->createMembership($groupA, $personId, $adminRoleId);
        $this->createMembership($groupB, $personId, $memberRoleId);

        $this->assertTrue($this->accessService->hasGroupPermission($user, $groupA, 'members.manage'));
        $this->assertFalse($this->accessService->hasGroupPermission($user, $groupB, 'members.manage'));

        $this->assertTrue($this->accessService->isGroupAdmin($user, $groupA));
        $this->assertFalse($this->accessService->isGroupAdmin($user, $groupB));
    }

    public function test_ordinary_member_cannot_use_admin_permission(): void
    {
        $user = $this->createUser('ordinary-member@example.test');

        $groupId = $this->createGroup('Ordinary Group', 'ordinary-group');
        $personId = $this->createPerson($user, 'Ordinary Member');

        $memberRoleId = $this->createRole('Family Member', 'family_member', 'group');

        $this->createMembership($groupId, $personId, $memberRoleId);

        $this->assertFalse($this->accessService->hasGroupPermission($user, $groupId, 'members.manage'));
        $this->assertFalse($this->accessService->isGroupAdmin($user, $groupId));
    }

    public function test_pending_membership_cannot_use_group_permission(): void
    {
        $user = $this->createUser('pending-admin@example.test');

        $groupId = $this->createGroup('Pending Group', 'pending-group');
        $personId = $this->createPerson($user, 'Pending Admin');

        $adminRoleId = $this->createRole('Family Admin', 'family_admin', 'group');
        $permissionId = $this->createPermission('members.manage');

        $this->grantPermission($adminRoleId, $permissionId);
        $this->createMembership($groupId, $personId, $adminRoleId, 'pending');

        $this->assertFalse($this->accessService->hasGroupPermission($user, $groupId, 'members.manage'));
    }

    public function test_inactive_group_cannot_grant_family_admin_permission(): void
    {
        $user = $this->createUser('inactive-admin@example.test');

        $groupId = $this->createGroup('Inactive Group', 'inactive-admin-group', 'inactive');
        $personId = $this->createPerson($user, 'Inactive Admin');

        $adminRoleId = $this->createRole('Family Admin', 'family_admin', 'group');
        $permissionId = $this->createPermission('members.manage');

        $this->grantPermission($adminRoleId, $permissionId);
        $this->createMembership($groupId, $personId, $adminRoleId);

        $this->assertFalse($this->accessService->hasGroupPermission($user, $groupId, 'members.manage'));
    }

    public function test_designated_super_admin_can_use_global_permission(): void
    {
        $user = $this->createUser('global-admin@example.test');

        $roleId = $this->createRole('Super Admin', 'super_admin', 'global');
        $permissionId = $this->createPermission('members.manage');

        $this->grantPermission($roleId, $permissionId);
        $this->designateSuperAdmin($user, $roleId);

        $this->assertTrue($this->accessService->hasGlobalPermission($user, 'members.manage'));
    }

    public function test_undesignated_super_admin_cannot_use_global_permission(): void
    {
        $user = $this->createUser('undesignated-admin@example.test');

        $roleId = $this->createRole('Super Admin', 'super_admin', 'global');
        $permissionId = $this->createPermission('members.manage');

        $this->grantPermission($roleId, $permissionId);

        DB::table('user_roles')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'role_scope' => 'global',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse($this->accessService->hasGlobalPermission($user, 'members.manage'));
    }

    public function test_family_admin_can_assign_member_role_but_not_admin_or_global_role(): void
    {
        $user = $this->createUser('role-manager@example.test');

        $groupId = $this->createGroup('Role Group', 'role-group');
        $personId = $this->createPerson($user, 'Role Manager');

        $adminRoleId = $this->createRole('Family Admin', 'family_admin', 'group');
        $memberRoleId = $this->createRole('Family Member', 'family_member', 'group');
        $globalRoleId = $this->createRole('Super Admin', 'super_admin', 'global');

        $permissionId = $this->createPermission('members.manage');

        $this->grantPermission($adminRoleId, $permissionId);
        $this->createMembership($groupId, $personId, $adminRoleId);

        $this->assertTrue($this->accessService->canAssignGroupRole($user, $groupId, $memberRoleId));
        $this->assertFalse($this->accessService->canAssignGroupRole($user, $groupId, $adminRoleId));
        $this->assertFalse($this->accessService->canAssignGroupRole($user, $groupId, $globalRoleId));
    }

    public function test_family_group_gate_enforces_group_specific_permission(): void
{
    $user = $this->createUser('gate-group-admin@example.test');

    $groupA = $this->createGroup('Gate Group A', 'gate-group-a');
    $groupB = $this->createGroup('Gate Group B', 'gate-group-b');

    $personId = $this->createPerson($user, 'Gate Admin');

    $adminRoleId = $this->createRole(
        'Family Admin',
        'family_admin',
        'group'
    );

    $memberRoleId = $this->createRole(
        'Family Member',
        'family_member',
        'group'
    );

    $permissionId = $this->createPermission('members.manage');

    $this->grantPermission($adminRoleId, $permissionId);

    $this->createMembership($groupA, $personId, $adminRoleId);
    $this->createMembership($groupB, $personId, $memberRoleId);

    $this->assertTrue(
        Gate::forUser($user)->allows(
            'family.group',
            [$groupA, 'members.manage']
        )
    );

    $this->assertFalse(
        Gate::forUser($user)->allows(
            'family.group',
            [$groupB, 'members.manage']
        )
    );
}

public function test_family_global_gate_requires_designated_super_admin(): void
{
    $user = $this->createUser('gate-super-admin@example.test');

    $roleId = $this->createRole(
        'Super Admin',
        'super_admin',
        'global'
    );

    $permissionId = $this->createPermission('groups.archive');

    $this->grantPermission($roleId, $permissionId);
    $this->designateSuperAdmin($user, $roleId);

    $this->assertTrue(
        Gate::forUser($user)->allows(
            'family.global',
            'groups.archive'
        )
    );

    $otherUser = $this->createUser('gate-other-user@example.test');

    $this->assertFalse(
        Gate::forUser($otherUser)->allows(
            'family.global',
            'groups.archive'
        )
    );
}

public function test_group_role_cannot_be_assigned_in_inactive_or_archived_group(): void
{
    $user = $this->createUser('inactive-role-manager@example.test');

    $inactiveGroup = $this->createGroup(
        'Inactive Role Group',
        'inactive-role-group',
        'inactive'
    );

    $archivedGroup = $this->createGroup(
        'Archived Role Group',
        'archived-role-group',
        'archived'
    );

    $personId = $this->createPerson($user, 'Role Manager');

    $adminRoleId = $this->createRole(
        'Family Admin',
        'family_admin',
        'group'
    );

    $memberRoleId = $this->createRole(
        'Family Member',
        'family_member',
        'group'
    );

    $permissionId = $this->createPermission('members.manage');

    $this->grantPermission($adminRoleId, $permissionId);

    $this->createMembership($inactiveGroup, $personId, $adminRoleId);
    $this->createMembership($archivedGroup, $personId, $adminRoleId);

    $this->assertFalse(
        $this->accessService->canAssignGroupRole(
            $user,
            $inactiveGroup,
            $memberRoleId
        )
    );

    $this->assertFalse(
        $this->accessService->canAssignGroupRole(
            $user,
            $archivedGroup,
            $memberRoleId
        )
    );
}

}
