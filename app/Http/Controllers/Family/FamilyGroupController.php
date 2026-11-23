<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\Family\FamilyGroup;
use App\Models\Family\FamilyPerson;
use App\Models\Family\Role;
use App\Services\Family\FamilyMembershipService;
use App\Services\Family\FamilyAccessService;
use App\Services\Family\FamilyGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;


class FamilyGroupController extends Controller
{
    public function __construct(
        private readonly FamilyAccessService $familyAccess,
        private readonly FamilyGroupService $groupService,
        private readonly FamilyMembershipService $membershipService
    ) {}


    public function create(Request $request): View
    {
        $user = $request->user('family');

        abort_unless(
            $this->familyAccess->hasGlobalPermission(
                $user,
                'groups.create'
            ),
            403
        );

        return view('family.groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user('family');

        $group = $this->groupService->create(
            $user,
            $request->only([
                'name',
                'group_type',
                'description',
            ])
        );

        return redirect()
            ->route('family.groups.show', $group->id)
            ->with(
                'success',
                'Family Group berhasil dibuat.'
            );
    }

    public function createMember(Request $request, int $groupId): View
{
    $user = $request->user('family');

    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasGroupPermission(
            $user,
            $group->id,
            'members.manage'
        ),
        403
    );

    abort_unless($group->status === 'active', 404);

    $isSuperAdmin = $this->familyAccess->isSuperAdmin($user);

    $rolesQuery = Role::query()
        ->where('scope', 'group');

    if ($isSuperAdmin) {
        $rolesQuery->whereIn('slug', [
            'family_admin',
            'family_member',
        ]);
    } else {
        $rolesQuery->where('slug', 'family_member');
    }

    $roles = $rolesQuery
        ->orderBy('name')
        ->get();

    $people = FamilyPerson::query()
        ->whereDoesntHave('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->orderBy('full_name')
        ->get();

    return view('family.groups.members.create', [
        'group' => $group,
        'people' => $people,
        'roles' => $roles,
    ]);
}

public function storeMember(
    Request $request,
    int $groupId
): RedirectResponse {
    $user = $request->user('family');

    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasGroupPermission(
            $user,
            $group->id,
            'members.manage'
        ),
        403
    );

    $validated = $request->validate([
        'person_id' => ['required', 'integer', 'exists:family_persons,id'],
        'role_id' => ['required', 'integer', 'exists:roles,id'],
        'reason' => ['nullable', 'string', 'max:500'],
    ]);

    abort_unless(
        $this->familyAccess->canAssignGroupRole(
            $user,
            $group->id,
            (int) $validated['role_id']
        ),
        403
    );

    $this->membershipService->addPendingMembership(
        $group->id,
        (int) $validated['person_id'],
        (int) $validated['role_id'],
        $user,
        $validated['reason'] ?? null
    );

    return redirect()
        ->route('family.groups.show', $group->id)
        ->with('success', 'Anggota baru berhasil ditambahkan dengan status menunggu.');
}

    public function show(Request $request, int $groupId)
    {
       $user = $request->user('family');
        $currentUserId = $user->id;

        $group = FamilyGroup::query()
            ->findOrFail($groupId);

        // Pastikan pengguna memiliki izin melihat grup ini.
        abort_unless(
            $this->familyAccess->hasGroupPermission(
                $user,
                $group->id,
                'groups.view'
            ),
            403
        );

        $canManageDetails = $this->familyAccess
            ->hasGroupPermission(
                $user,
                $group->id,
                'groups.update'
            );

        $canManageMembers = $this->familyAccess
            ->hasGroupPermission(
                $user,
                $group->id,
                'members.manage'
            );

        $isSuperAdmin = $this->familyAccess->isSuperAdmin($user);

        $canManageGroupSettings = $isSuperAdmin
            && $this->familyAccess->hasGlobalPermission(
                $user,
                'groups.type.manage'
            )
            && $this->familyAccess->hasGlobalPermission(
                $user,
                'groups.activate'
            );

        // Ambil anggota sesuai kewenangan pengguna.
        $membersQuery = $group->memberships()
            ->with(['person', 'role']);

        if (! $canManageMembers) {
            $membersQuery->where('status', 'active');
        }

        $members = $membersQuery
            ->orderByRaw("
                CASE status
                    WHEN 'pending' THEN 1
                    WHEN 'active' THEN 2
                    WHEN 'inactive' THEN 3
                    WHEN 'left' THEN 4
                    WHEN 'rejected' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('joined_at')
            ->orderBy('id')
            ->get();

        $activeMembersCount = $members
            ->where('status', 'active')
            ->count();

            // Data untuk modal tambah anggota.
$people = collect();
$roles = collect();

if ($canManageMembers && $group->status === 'active') {
    $isSuperAdmin = $this->familyAccess->isSuperAdmin($user);

    $rolesQuery = Role::query()
        ->where('scope', 'group');

    if ($isSuperAdmin) {
        $rolesQuery->whereIn('slug', [
            'family_admin',
            'family_member',
        ]);
    } else {
        $rolesQuery->where('slug', 'family_member');
    }

    $roles = $rolesQuery
        ->orderBy('name')
        ->get();

    $people = FamilyPerson::query()
        ->whereDoesntHave('memberships', function ($query) use ($group) {
            $query->where('family_group_id', $group->id);
        })
        ->orderBy('full_name')
        ->get();
}

            return view('family.groups.show', [
            'group' => $group,
            'members' => $members,
            'activeMembersCount' => $activeMembersCount,
            'canManageDetails' => $canManageDetails,
            'canManageMembers' => $canManageMembers,
            'isSuperAdmin' => $isSuperAdmin,
            'canManageGroupSettings' => $canManageGroupSettings,
            'people' => $people,
            'roles' => $roles,
            'currentUserId' => $currentUserId,
        ]);
            }

    public function edit(Request $request, int $groupId): View
{
    $user = $request->user('family');

    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasGroupPermission(
            $user,
            $group->id,
            'groups.update'
        ),
        403
    );

    abort_if(
        $group->status === 'archived',
        404
    );

    return view('family.groups.edit', [
        'group' => $group,
    ]);
        }

        public function update(
            Request $request,
            int $groupId
        ): RedirectResponse {
            $user = $request->user('family');

            $group = $this->groupService->updateDetails(
                $user,
                $groupId,
                $request->only(['name', 'description'])
            );

            return redirect()
                ->route('family.groups.show', $group->id)
                ->with(
                    'success',
                    'Informasi Family Group berhasil diperbarui.'
                );
        }


    public function changeType(
        Request $request,
        int $groupId
    ): RedirectResponse {
        $validated = $request->validate([
            'group_type' => ['required', 'string', 'max:30'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $group = $this->groupService->changeType(
            $request->user('family'),
            $groupId,
            $validated['group_type'],
            $validated['reason'] ?? null
        );

        return redirect()
            ->route('family.groups.show', $group->id)
            ->with('success', 'Tipe Family Group berhasil diperbarui.');
    }

    public function changeStatus(
        Request $request,
        int $groupId
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $group = $this->groupService->changeStatus(
            $request->user('family'),
            $groupId,
            $validated['status'],
            $validated['reason'] ?? null
        );

        return redirect()
            ->route('family.groups.show', $group->id)
            ->with('success', 'Status Family Group berhasil diperbarui.');
    }

    public function archive(
        Request $request,
        int $groupId
    ): RedirectResponse {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $group = $this->groupService->archive(
            $request->user('family'),
            $groupId,
            $validated['reason'] ?? null
        );

        return redirect()
            ->route('family.groups.show', $group->id)
            ->with('success', 'Family Group berhasil diarsipkan.');
    }

    public function restore(
        Request $request,
        int $groupId
    ): RedirectResponse {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $group = $this->groupService->restore(
            $request->user('family'),
            $groupId,
            $validated['reason'] ?? null
        );

        return redirect()
            ->route('family.groups.show', $group->id)
            ->with('success', 'Family Group berhasil dipulihkan.');
    }



public function changeMemberStatus(
    Request $request,
    int $groupId,
    int $membershipId
): RedirectResponse {
    $user = $request->user('family');

    $group = FamilyGroup::query()->findOrFail($groupId);

    abort_unless(
        $this->familyAccess->hasGroupPermission(
            $user,
            $group->id,
            'members.manage'
        ),
        403
    );

    $membership = $group->memberships()
        ->whereKey($membershipId)
        ->firstOrFail();

    // Prevent users from changing their own membership.
        $isOwnMembership = FamilyPerson::query()
            ->where('id', $membership->family_person_id)
            ->where('user_id', $user->id)
            ->exists();

        abort_if(
            $isOwnMembership,
            403,
            'You cannot change your own membership status.'
        );

    $validated = $request->validate([
        'status' => [
            'required',
            'in:active,rejected,inactive,left',
        ],
        'role_id' => [
            'nullable',
            'integer',
            'exists:roles,id',
        ],
        'reason' => ['nullable', 'string', 'max:500'],
    ]);

    $newStatus = $validated['status'];
    $roleId = null;

    if ($newStatus === 'active') {
        $roleId = (int) (
            $validated['role_id'] ?? $membership->role_id
        );

        abort_unless(
            $this->familyAccess->canAssignGroupRole(
                $user,
                $group->id,
                $roleId
            ),
            403
        );
    }

    $this->membershipService->changeStatus(
        $membership->id,
        $newStatus,
        $user,
        $validated['reason'] ?? null,
        $roleId
    );

    return redirect()
        ->route('family.groups.show', $group->id)
        ->with(
            'success',
            'Status keanggotaan berhasil diperbarui.'
        );
}


}