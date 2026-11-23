<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\Family\FamilyGroup;
use App\Models\Family\FamilyPerson;
use App\Services\Family\FamilyAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FamilyAccessService $familyAccess
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user('family');
        $isSuperAdmin = $this->familyAccess->isSuperAdmin($user);
        $canViewAllGroups = $this->familyAccess
            ->hasGlobalPermission($user, 'groups.view');

        $groupsQuery = FamilyGroup::query()
            ->withCount([
                'memberships as active_members_count' =>
                    fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('name');

        if (! $canViewAllGroups) {
            $groupsQuery
                ->where('status', 'active')
                ->whereHas('memberships', function ($query) use ($user) {
                    $query
                        ->where('status', 'active')
                        ->whereHas('person', function ($personQuery) use ($user) {
                            $personQuery->where('user_id', $user->id);
                        });
                });
        }

        $groups = $groupsQuery->get();

        // A selected group must exist in the already-authorized collection.
        $selectedGroup = null;
        $requestedGroupId = $request->query('group');

        if ($requestedGroupId !== null && $requestedGroupId !== '') {
            abort_unless(
                ctype_digit((string) $requestedGroupId),
                404
            );

            $selectedGroup = $groups->firstWhere(
                'id',
                (int) $requestedGroupId
            );

            abort_unless($selectedGroup, 404);
        }

        $visibleGroups = $selectedGroup
            ? collect([$selectedGroup])
            : $groups;

        $groupIds = $visibleGroups->pluck('id');

        $groupRoles = collect();

        if ($isSuperAdmin) {
            // Super Admin memiliki akses global.
            $groupRoles = $visibleGroups->mapWithKeys(
                fn ($group) => [
                    $group->id => 'super_admin',
                ]
            );
        } elseif ($groupIds->isNotEmpty()) {
            // Ambil peran aktif pengguna pada setiap grup.
            $groupRoles = DB::table('family_members')
                ->join(
                    'family_persons',
                    'family_persons.id',
                    '=',
                    'family_members.family_person_id'
                )
                ->join(
                    'roles',
                    'roles.id',
                    '=',
                    'family_members.role_id'
                )
                ->where('family_persons.user_id', $user->id)
                ->whereIn('family_members.family_group_id', $groupIds)
                ->where('family_members.status', 'active')
                ->where('family_members.role_scope', 'group')
                ->where('roles.scope', 'group')
                ->pluck(
                    'roles.slug',
                    'family_members.family_group_id'
                );
        }


        $activeMemberCount = $groupIds->isEmpty()
            ? 0
            : DB::table('family_members')
                ->whereIn('family_group_id', $groupIds)
                ->where('status', 'active')
                ->distinct()
                ->count('family_person_id');

        $person = FamilyPerson::query()
            ->where('user_id', $user->id)
            ->first();

        $displayName = $person?->nickname
            ?: $person?->full_name
            ?: $user->name;

        return view('family.dashboard', [
            'groups' => $groups,
            'selectedGroup' => $selectedGroup,
            'visibleGroups' => $visibleGroups,
            'groupCount' => $visibleGroups->count(),
            'activeGroupCount' => $visibleGroups
                ->where('status', 'active')
                ->count(),
            'activeMemberCount' => $activeMemberCount,
            'displayName' => $displayName,
            'canViewAllGroups' => $canViewAllGroups,
            'isSuperAdmin' => $isSuperAdmin,
            'groupRoles' => $groupRoles,
            'canCreateGroup' => $this->familyAccess
              ->hasGlobalPermission($user, 'groups.create'),
        ]);
    }
}
