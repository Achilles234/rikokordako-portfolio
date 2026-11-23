<?php

namespace Tests\Feature\Family;

use App\Models\User;
use Database\Seeders\FamilyPortalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FamilyMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FamilyPortalSeeder::class);
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'test-password',
        ]);
    }

    private function createGroup(string $name): int
    {
        return DB::table('family_groups')->insertGetId([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'group_type' => 'custom',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPerson(?int $userId, string $name): int
    {
        return DB::table('family_persons')->insertGetId([
            'user_id' => $userId,
            'full_name' => $name,
            'life_status' => 'alive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function roleId(string $slug): int
    {
        return (int) DB::table('roles')
            ->where('slug', $slug)
            ->value('id');
    }

    private function createMembership(
        int $groupId,
        int $personId,
        int $roleId,
        string $status = 'pending'
    ): int {
        return DB::table('family_members')->insertGetId([
            'family_group_id' => $groupId,
            'family_person_id' => $personId,
            'role_id' => $roleId,
            'role_scope' => 'group',
            'status' => $status,
            'joined_at' => in_array($status, ['active', 'inactive'], true)
                ? now()
                : null,
            'left_at' => $status === 'left' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createFamilyAdmin(string $email): array
    {
        $user = $this->createUser($email);
        $groupId = $this->createGroup('Admin Group '.uniqid());

        $personId = $this->createPerson(
            $user->id,
            'Family Admin'
        );

        $membershipId = $this->createMembership(
            $groupId,
            $personId,
            $this->roleId('family_admin'),
            'active'
        );

        return [$user, $groupId, $membershipId];
    }

    private function createSuperAdmin(string $email): User
    {
        $user = $this->createUser($email);

        $roleId = $this->roleId('super_admin');

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

    public function test_family_admin_can_approve_pending_member_in_own_group(): void
    {
        [$admin, $groupId] = $this->createFamilyAdmin(
            'admin-approve@example.test'
        );

        $personId = $this->createPerson(null, 'Pending Person');

        $membershipId = $this->createMembership(
            $groupId,
            $personId,
            $this->roleId('family_member')
        );

        $response = $this->actingAs($admin, 'family')
            ->patch(route('family.groups.members.status', [
                'groupId' => $groupId,
                'membershipId' => $membershipId,
            ]), [
                'status' => 'active',
                'reason' => 'Permintaan disetujui',
            ]);

        $response->assertRedirect(
            route('family.groups.show', $groupId)
        );

        $this->assertDatabaseHas('family_members', [
            'id' => $membershipId,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('family_membership_status_logs', [
            'family_member_id' => $membershipId,
            'from_status' => 'pending',
            'to_status' => 'active',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_family_admin_cannot_manage_member_in_another_group(): void
    {
        [$admin] = $this->createFamilyAdmin(
            'admin-other-group@example.test'
        );

        $otherGroupId = $this->createGroup('Other Group');

        $personId = $this->createPerson(null, 'Other Group Person');

        $membershipId = $this->createMembership(
            $otherGroupId,
            $personId,
            $this->roleId('family_member')
        );

        $response = $this->actingAs($admin, 'family')
            ->patch(route('family.groups.members.status', [
                'groupId' => $otherGroupId,
                'membershipId' => $membershipId,
            ]), [
                'status' => 'active',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('family_members', [
            'id' => $membershipId,
            'status' => 'pending',
        ]);
    }

    public function test_family_admin_cannot_assign_family_admin_role(): void
    {
        [$admin, $groupId] = $this->createFamilyAdmin(
            'admin-role-test@example.test'
        );

        $personId = $this->createPerson(null, 'Former Admin');

        $membershipId = $this->createMembership(
            $groupId,
            $personId,
            $this->roleId('family_admin'),
            'left'
        );

        $response = $this->actingAs($admin, 'family')
            ->patch(route('family.groups.members.status', [
                'groupId' => $groupId,
                'membershipId' => $membershipId,
            ]), [
                'status' => 'active',
                'role_id' => $this->roleId('family_admin'),
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('family_members', [
            'id' => $membershipId,
            'status' => 'left',
        ]);
    }

    public function test_super_admin_can_manage_member_in_another_group(): void
    {
        $superAdmin = $this->createSuperAdmin(
            'superadmin-manage@example.test'
        );

        $groupId = $this->createGroup('Super Admin Target Group');

        $personId = $this->createPerson(null, 'Pending Member');

        $membershipId = $this->createMembership(
            $groupId,
            $personId,
            $this->roleId('family_member')
        );

        $response = $this->actingAs($superAdmin, 'family')
            ->patch(route('family.groups.members.status', [
                'groupId' => $groupId,
                'membershipId' => $membershipId,
            ]), [
                'status' => 'active',
                'reason' => 'Disetujui Super Admin',
            ]);

        $response->assertRedirect(
            route('family.groups.show', $groupId)
        );

        $this->assertDatabaseHas('family_members', [
            'id' => $membershipId,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('family_membership_status_logs', [
            'family_member_id' => $membershipId,
            'from_status' => 'pending',
            'to_status' => 'active',
            'changed_by' => $superAdmin->id,
        ]);
    }

    public function test_ordinary_family_member_cannot_manage_membership(): void
    {
        $user = $this->createUser('ordinary-member@example.test');

        $groupId = $this->createGroup('Ordinary Member Group');

        $personId = $this->createPerson(
            $user->id,
            'Ordinary Member'
        );

        $this->createMembership(
            $groupId,
            $personId,
            $this->roleId('family_member'),
            'active'
        );

        $targetPersonId = $this->createPerson(
            null,
            'Target Member'
        );

        $targetMembershipId = $this->createMembership(
            $groupId,
            $targetPersonId,
            $this->roleId('family_member')
        );

        $response = $this->actingAs($user, 'family')
            ->patch(route('family.groups.members.status', [
                'groupId' => $groupId,
                'membershipId' => $targetMembershipId,
            ]), [
                'status' => 'active',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('family_members', [
            'id' => $targetMembershipId,
            'status' => 'pending',
        ]);
    }

    public function test_family_admin_cannot_deactivate_own_membership(): void
{
    [$admin, $groupId, $membershipId] = $this->createFamilyAdmin(
        'admin-self-deactivate@example.test'
    );

    $response = $this->actingAs($admin, 'family')
        ->patch(route('family.groups.members.status', [
            'groupId' => $groupId,
            'membershipId' => $membershipId,
        ]), [
            'status' => 'inactive',
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('family_members', [
        'id' => $membershipId,
        'status' => 'active',
    ]);
}

public function test_family_admin_cannot_mark_own_membership_as_left(): void
{
    [$admin, $groupId, $membershipId] = $this->createFamilyAdmin(
        'admin-self-leave@example.test'
    );

    $response = $this->actingAs($admin, 'family')
        ->patch(route('family.groups.members.status', [
            'groupId' => $groupId,
            'membershipId' => $membershipId,
        ]), [
            'status' => 'left',
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('family_members', [
        'id' => $membershipId,
        'status' => 'active',
    ]);
}
}
