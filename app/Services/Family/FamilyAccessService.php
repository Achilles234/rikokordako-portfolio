<?php

namespace App\Services\Family;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class FamilyAccessService
{
    /**
     * Determine whether a user may enter the Family Portal.
     */
    public function canAccess(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isActiveFamilyMember($user);
    }

    /**
     * Check the explicitly designated Super Admin identity
     * and its required global role.
     */
    public function isSuperAdmin(User $user): bool
    {
        $isDesignatedSuperAdmin = DB::table('family_portal_settings')
            ->where('id', 1)
            ->where('super_admin_user_id', $user->id)
            ->exists();

        if (! $isDesignatedSuperAdmin) {
            return false;
        }

        return DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $user->id)
            ->where('user_roles.role_scope', 'global')
            ->where('roles.slug', 'super_admin')
            ->where('roles.scope', 'global')
            ->exists();
    }

    /**
     * Check active membership in at least one active family group.
     */
    public function isActiveFamilyMember(User $user): bool
    {
        return DB::table('family_persons')
            ->join(
                'family_members',
                'family_members.family_person_id',
                '=',
                'family_persons.id'
            )
            ->join(
                'family_groups',
                'family_groups.id',
                '=',
                'family_members.family_group_id'
            )
            ->join(
                'roles',
                'roles.id',
                '=',
                'family_members.role_id'
            )
            ->where('family_persons.user_id', $user->id)
            ->where('family_members.status', 'active')
            ->where('family_members.role_scope', 'group')
            ->where('family_groups.status', 'active')
            ->where('roles.scope', 'group')
            ->exists();
    }

    /**
     * Check a permission.
     *
     * Without a group ID, only global permissions are checked.
     * With a group ID, the permission is checked in that group.
     */
    public function hasPermission(
        User $user,
        string $permissionSlug,
        ?int $groupId = null
    ): bool {
        if ($groupId === null) {
            return $this->hasGlobalPermission($user, $permissionSlug);
        }

        return $this->hasGroupPermission(
            $user,
            $groupId,
            $permissionSlug
        );
    }

    /**
     * Check a permission granted through the designated
     * Super Admin's global role.
     */
    public function hasGlobalPermission(
        User $user,
        string $permissionSlug
    ): bool {
        if (! $this->isSuperAdmin($user)) {
            return false;
        }

        return DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->join(
                'role_permissions',
                'role_permissions.role_id',
                '=',
                'roles.id'
            )
            ->join(
                'permissions',
                'permissions.id',
                '=',
                'role_permissions.permission_id'
            )
            ->where('user_roles.user_id', $user->id)
            ->where('user_roles.role_scope', 'global')
            ->where('roles.scope', 'global')
            ->where('roles.slug', 'super_admin')
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    /**
     * Check a permission within one specific active group.
     *
     * A Family Admin's permission in another group is never
     * considered. Super Admin global permissions are also
     * recognized for existing groups, including inactive ones.
     */
    public function hasGroupPermission(
        User $user,
        int $groupId,
        string $permissionSlug
    ): bool {
        $groupExists = DB::table('family_groups')
            ->where('id', $groupId)
            ->exists();

        if (! $groupExists) {
            return false;
        }

        if ($this->hasGlobalPermission($user, $permissionSlug)) {
            return true;
        }

        return DB::table('family_persons')
            ->join(
                'family_members',
                'family_members.family_person_id',
                '=',
                'family_persons.id'
            )
            ->join(
                'family_groups',
                'family_groups.id',
                '=',
                'family_members.family_group_id'
            )
            ->join(
                'roles',
                'roles.id',
                '=',
                'family_members.role_id'
            )
            ->join(
                'role_permissions',
                'role_permissions.role_id',
                '=',
                'roles.id'
            )
            ->join(
                'permissions',
                'permissions.id',
                '=',
                'role_permissions.permission_id'
            )
            ->where('family_persons.user_id', $user->id)
            ->where('family_members.family_group_id', $groupId)
            ->where('family_members.status', 'active')
            ->where('family_members.role_scope', 'group')
            ->where('family_groups.status', 'active')
            ->where('roles.scope', 'group')
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    /**
     * Determine whether the user is a Family Admin
     * in the specified active group.
     */
    public function isGroupAdmin(User $user, int $groupId): bool
    {
        return DB::table('family_persons')
            ->join(
                'family_members',
                'family_members.family_person_id',
                '=',
                'family_persons.id'
            )
            ->join(
                'family_groups',
                'family_groups.id',
                '=',
                'family_members.family_group_id'
            )
            ->join(
                'roles',
                'roles.id',
                '=',
                'family_members.role_id'
            )
            ->where('family_persons.user_id', $user->id)
            ->where('family_members.family_group_id', $groupId)
            ->where('family_members.status', 'active')
            ->where('family_members.role_scope', 'group')
            ->where('family_groups.status', 'active')
            ->where('roles.scope', 'group')
            ->where('roles.slug', 'family_admin')
            ->exists();
    }

    /**
     * Check whether an actor may assign a group role.
     *
     * Family Admin may assign only the ordinary family_member
     * role. Assigning family_admin is reserved for Super Admin.
     * Global roles can never be assigned through this method.
     */
    public function canAssignGroupRole(
        User $actor,
        int $groupId,
        int $targetRoleId
    ): bool {
        $targetRole = DB::table('roles')
            ->where('id', $targetRoleId)
            ->first();

        if (! $targetRole || $targetRole->scope !== 'group') {
            return false;
        }

                if (! DB::table('family_groups')
            ->where('id', $groupId)
            ->where('status', 'active')
            ->exists()) {
            return false;
        }

        if ($this->hasGlobalPermission($actor, 'members.manage')) {
            return true;
        }

        if (! $this->hasGroupPermission($actor, $groupId, 'members.manage')) {
            return false;
        }

        return $targetRole->slug === 'family_member';
    }


}
