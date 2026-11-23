<?php

namespace Tests\Feature\Family;

use App\Models\Family\FamilyGroup;
use App\Models\Family\FamilyMember;
use App\Models\Family\FamilyMembershipStatusLog;
use App\Models\Family\FamilyPerson;
use App\Models\Family\Role;
use App\Models\User;
use App\Services\Family\FamilyMembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FamilyMembershipServiceTest extends TestCase
{
    use RefreshDatabase;

    private FamilyMembershipService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FamilyMembershipService::class);
    }

    private function createRole(
        string $slug = 'member',
        string $scope = 'group'
    ): Role {
        return Role::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'scope' => $scope,
            'is_system' => false,
        ]);
    }

    private function createMembership(
        string $status = 'pending'
    ): FamilyMember {
        $role = $this->createRole();

        $group = FamilyGroup::create([
            'name' => 'Keluarga Test',
            'slug' => 'keluarga-test',
            'group_type' => 'custom',
            'status' => 'active',
        ]);

        $person = FamilyPerson::create([
            'full_name' => 'Anggota Test',
            'life_status' => 'alive',
        ]);

        $joinedAt = in_array($status, ['active', 'inactive', 'left'], true)
            ? now()->subDays(30)
            : null;

        $leftAt = $status === 'left'
            ? now()->subDays(5)
            : null;

        return FamilyMember::create([
            'family_group_id' => $group->id,
            'family_person_id' => $person->id,
            'role_id' => $role->id,
            'status' => $status,
            'joined_at' => $joinedAt,
            'left_at' => $leftAt,
        ]);
    }

    private function createActor(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.com',
            'password' => 'password',
        ]);
    }

    public function test_it_activates_pending_membership_and_records_log(): void
    {
        $membership = $this->createMembership('pending');
        $actor = $this->createActor();

        $membership = $this->service->changeStatus(
            $membership->id,
            'active',
            $actor,
            'Permintaan disetujui',
            $membership->role_id
        );

        $this->assertSame('active', $membership->status);
        $this->assertNotNull($membership->joined_at);
        $this->assertNull($membership->left_at);

        $this->assertDatabaseHas('family_membership_status_logs', [
            'family_member_id' => $membership->id,
            'from_status' => 'pending',
            'to_status' => 'active',
            'changed_by' => $actor->id,
            'reason' => 'Permintaan disetujui',
        ]);
    }

    public function test_it_rejects_pending_membership_and_records_log(): void
    {
        $membership = $this->createMembership('pending');

        $membership = $this->service->changeStatus(
            $membership->id,
            'rejected',
            null,
            'Permintaan tidak disetujui'
        );

        $this->assertSame('rejected', $membership->status);
        $this->assertNull($membership->joined_at);
        $this->assertNull($membership->left_at);

        $this->assertDatabaseHas('family_membership_status_logs', [
            'family_member_id' => $membership->id,
            'from_status' => 'pending',
            'to_status' => 'rejected',
        ]);
    }

    public function test_it_marks_active_membership_as_left(): void
    {
        $membership = $this->createMembership('active');
        $originalJoinedAt = $membership->joined_at->toDateTimeString();

        $membership = $this->service->changeStatus(
            $membership->id,
            'left',
            null,
            'Anggota keluar'
        );

        $this->assertSame('left', $membership->status);
        $this->assertSame(
            $originalJoinedAt,
            $membership->joined_at->toDateTimeString()
        );
        $this->assertNotNull($membership->left_at);

        $this->assertDatabaseHas('family_membership_status_logs', [
            'family_member_id' => $membership->id,
            'from_status' => 'active',
            'to_status' => 'left',
        ]);
    }

    public function test_it_reactivates_former_member_using_same_membership_record(): void
    {
        $membership = $this->createMembership('left');
        $originalId = $membership->id;

        $newRole = $this->createRole('family-admin');

        $membership = $this->service->changeStatus(
            $membership->id,
            'active',
            null,
            'Bergabung kembali',
            $newRole->id
        );

        $this->assertSame($originalId, $membership->id);
        $this->assertSame('active', $membership->status);
        $this->assertEquals($newRole->id, $membership->role_id);
        $this->assertNull($membership->left_at);
        $this->assertTrue(
            $membership->joined_at->greaterThan(now()->subSeconds(10))
        );

        $this->assertDatabaseCount('family_members', 1);

        $this->assertDatabaseHas('family_membership_status_logs', [
            'family_member_id' => $membership->id,
            'from_status' => 'left',
            'to_status' => 'active',
            'reason' => 'Bergabung kembali',
        ]);
    }

    public function test_it_requires_role_when_activating_membership(): void
    {
        $membership = $this->createMembership('pending');

        try {
            $this->service->changeStatus(
                $membership->id,
                'active'
            );

            $this->fail('Role wajib ditentukan saat aktivasi.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('role_id', $exception->errors());
        }

        $this->assertSame(
            'pending',
            $membership->fresh()->status
        );

        $this->assertDatabaseCount('family_membership_status_logs', 0);
    }

    public function test_it_rejects_role_outside_group_scope(): void
    {
        $membership = $this->createMembership('pending');

        $globalRole = $this->createRole(
            'global-test',
            'global'
        );

        try {
            $this->service->changeStatus(
                $membership->id,
                'active',
                null,
                null,
                $globalRole->id
            );

            $this->fail('Role global tidak boleh dipakai sebagai role grup.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('role_id', $exception->errors());
        }

        $this->assertSame(
            'pending',
            $membership->fresh()->status
        );
    }

    public function test_it_preserves_joined_date_when_inactive_member_returns(): void
    {
        $membership = $this->createMembership('inactive');
        $originalJoinedAt = $membership->joined_at->toDateTimeString();

        $membership = $this->service->changeStatus(
            $membership->id,
            'active',
            null,
            null,
            $membership->role_id
        );

        $this->assertSame('active', $membership->status);
        $this->assertSame(
            $originalJoinedAt,
            $membership->joined_at->toDateTimeString()
        );
        $this->assertNull($membership->left_at);
    }

    public function test_it_rejects_invalid_status_transition(): void
    {
        $membership = $this->createMembership('rejected');

        $this->expectException(ValidationException::class);

        $this->service->changeStatus(
            $membership->id,
            'active',
            null,
            null,
            $membership->role_id
        );
    }


public function test_it_marks_active_membership_as_inactive_without_changing_joined_date(): void
{
    $membership = $this->createMembership('active');
    $originalJoinedAt = $membership->joined_at->toDateTimeString();

    $membership = $this->service->changeStatus(
        $membership->id,
        'inactive',
        null,
        'Keanggotaan dinonaktifkan sementara'
    );

    $this->assertSame('inactive', $membership->status);
    $this->assertSame(
        $originalJoinedAt,
        $membership->joined_at->toDateTimeString()
    );
    $this->assertNull($membership->left_at);

    $this->assertDatabaseHas('family_membership_status_logs', [
        'family_member_id' => $membership->id,
        'from_status' => 'active',
        'to_status' => 'inactive',
    ]);
}

public function test_it_marks_inactive_membership_as_left(): void
{
    $membership = $this->createMembership('inactive');
    $originalJoinedAt = $membership->joined_at->toDateTimeString();

    $membership = $this->service->changeStatus(
        $membership->id,
        'left',
        null,
        'Anggota keluar'
    );

    $this->assertSame('left', $membership->status);
    $this->assertSame(
        $originalJoinedAt,
        $membership->joined_at->toDateTimeString()
    );
    $this->assertNotNull($membership->left_at);
    $this->assertTrue(
        $membership->left_at->greaterThanOrEqualTo($membership->joined_at)
    );

    $this->assertDatabaseHas('family_membership_status_logs', [
        'family_member_id' => $membership->id,
        'from_status' => 'inactive',
        'to_status' => 'left',
    ]);
}

public function test_it_rolls_back_membership_status_when_audit_log_creation_fails(): void
{
    $membership = $this->createMembership('active');

    $invalidActor = new User();
    $invalidActor->id = 999999;

    try {
        $this->service->changeStatus(
            $membership->id,
            'left',
            $invalidActor,
            'Testing audit rollback'
        );

        $this->fail('Pencatatan audit seharusnya gagal karena actor tidak valid.');
    } catch (\Illuminate\Database\QueryException $exception) {
        $this->assertSame(
            'active',
            $membership->fresh()->status
        );

        $this->assertNull($membership->fresh()->left_at);

        $this->assertDatabaseCount(
            'family_membership_status_logs',
            0
        );
    }
}

    public function test_it_creates_pending_membership_and_records_initial_log(): void
{
    $role = $this->createRole();
    $group = FamilyGroup::create([
        'name' => 'Keluarga Baru',
        'slug' => 'keluarga-baru',
        'group_type' => 'custom',
        'status' => 'active',
    ]);
    $person = FamilyPerson::create([
        'full_name' => 'Anggota Baru',
        'life_status' => 'alive',
    ]);
    $actor = $this->createActor();

    $membership = $this->service->addPendingMembership(
        $group->id,
        $person->id,
        $role->id,
        $actor,
        'Penambahan anggota baru'
    );

    $this->assertSame('pending', $membership->status);
    $this->assertNull($membership->joined_at);
    $this->assertNull($membership->left_at);

    $this->assertDatabaseHas('family_membership_status_logs', [
        'family_member_id' => $membership->id,
        'from_status' => null,
        'to_status' => 'pending',
        'changed_by' => $actor->id,
        'reason' => 'Penambahan anggota baru',
    ]);
}

public function test_it_rejects_duplicate_membership(): void
{
    $membership = $this->createMembership('active');

    $this->expectException(ValidationException::class);

    $this->service->addPendingMembership(
        $membership->family_group_id,
        $membership->family_person_id,
        $membership->role_id
    );
}

public function test_it_rejects_global_role_when_creating_membership(): void
{
    $role = $this->createRole('global-test', 'global');
    $group = FamilyGroup::create([
        'name' => 'Keluarga Global Test',
        'slug' => 'keluarga-global-test',
        'group_type' => 'custom',
        'status' => 'active',
    ]);
    $person = FamilyPerson::create([
        'full_name' => 'Anggota Global Test',
        'life_status' => 'alive',
    ]);

    try {
        $this->service->addPendingMembership(
            $group->id,
            $person->id,
            $role->id
        );

        $this->fail('Role global tidak boleh digunakan.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('role_id', $exception->errors());
    }

    $this->assertDatabaseCount('family_members', 0);
}

public function test_it_rejects_membership_creation_for_inactive_group(): void
{
    $role = $this->createRole();
    $group = FamilyGroup::create([
        'name' => 'Keluarga Nonaktif',
        'slug' => 'keluarga-nonaktif',
        'group_type' => 'custom',
        'status' => 'inactive',
    ]);
    $person = FamilyPerson::create([
        'full_name' => 'Anggota Nonaktif',
        'life_status' => 'alive',
    ]);

    try {
        $this->service->addPendingMembership(
            $group->id,
            $person->id,
            $role->id
        );

        $this->fail('Grup nonaktif tidak boleh menerima anggota baru.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('group', $exception->errors());
    }

    $this->assertDatabaseCount('family_members', 0);
}

public function test_it_requires_reactivation_for_former_member(): void
{
    $membership = $this->createMembership('left');

    try {
        $this->service->addPendingMembership(
            $membership->family_group_id,
            $membership->family_person_id,
            $membership->role_id
        );

        $this->fail('Anggota lama harus menggunakan proses reaktivasi.');
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('person_id', $exception->errors());
    }

    $this->assertDatabaseCount('family_members', 1);
}

}
