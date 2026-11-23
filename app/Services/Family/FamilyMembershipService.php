<?php

namespace App\Services\Family;

use App\Models\Family\FamilyMember;
use App\Models\Family\FamilyMembershipStatusLog;
use App\Models\Family\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Family\FamilyGroup;
use App\Models\Family\FamilyPerson;

class FamilyMembershipService
{
    /**
     * Allowed membership status transitions.
     */
    private const ALLOWED_TRANSITIONS = [
        'pending' => ['active', 'rejected'],
        'active' => ['inactive', 'left'],
        'inactive' => ['active', 'left'],
        'left' => ['active'],
        'rejected' => [],
    ];

   public function addPendingMembership(
    int $groupId,
    int $personId,
    int $roleId,
    ?User $actor = null,
    ?string $reason = null
): FamilyMember {
    return DB::transaction(function () use (
        $groupId,
        $personId,
        $roleId,
        $actor,
        $reason
    ) {
        $group = FamilyGroup::query()
            ->whereKey($groupId)
            ->lockForUpdate()
            ->firstOrFail();

        if ($group->status !== 'active') {
            throw ValidationException::withMessages([
                'group' => 'Anggota hanya dapat ditambahkan ke grup aktif.',
            ]);
        }

        $person = FamilyPerson::query()
            ->whereKey($personId)
            ->lockForUpdate()
            ->firstOrFail();

        $role = Role::query()
            ->whereKey($roleId)
            ->where('scope', 'group')
            ->first();

        if (! $role) {
            throw ValidationException::withMessages([
                'role_id' => 'Role tidak valid untuk keanggotaan grup.',
            ]);
        }

        $existingMembership = FamilyMember::query()
            ->where('family_group_id', $group->id)
            ->where('family_person_id', $person->id)
            ->lockForUpdate()
            ->first();

        if ($existingMembership) {
            throw ValidationException::withMessages([
                'person_id' => $existingMembership->status === 'left'
                    ? 'Individu ini pernah menjadi anggota grup. Gunakan fitur bergabung kembali.'
                    : 'Individu ini sudah memiliki keanggotaan di grup tersebut.',
            ]);
        }

        $membership = FamilyMember::create([
            'family_group_id' => $group->id,
            'family_person_id' => $person->id,
            'role_id' => $role->id,
            'status' => 'pending',
            'joined_at' => null,
            'left_at' => null,
        ]);

        $log = new FamilyMembershipStatusLog();
        $log->from_status = null;
        $log->to_status = 'pending';
        $log->changed_by = $actor?->id;
        $log->reason = $reason;

        $membership->statusLogs()->save($log);

        return $membership->refresh();
    });
}
    public function changeStatus(
        int $membershipId,
        string $newStatus,
        ?User $actor = null,
        ?string $reason = null,
        ?int $roleId = null
    ): FamilyMember {
        return DB::transaction(function () use (
            $membershipId,
            $newStatus,
            $actor,
            $reason,
            $roleId
        ) {
            $membership = FamilyMember::query()
                ->whereKey($membershipId)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $membership->status;

            if (! in_array(
                $newStatus,
                self::ALLOWED_TRANSITIONS[$oldStatus] ?? [],
                true
            )) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status keanggotaan tidak diperbolehkan.',
                ]);
            }

            $now = now();

            // Every activation requires an explicitly reviewed group role.
            if ($newStatus === 'active') {
                if ($roleId === null) {
                    throw ValidationException::withMessages([
                        'role_id' => 'Role wajib ditentukan saat mengaktifkan anggota.',
                    ]);
                }

                $validRole = Role::query()
                    ->whereKey($roleId)
                    ->where('scope', 'group')
                    ->exists();

                if (! $validRole) {
                    throw ValidationException::withMessages([
                        'role_id' => 'Role tidak valid untuk keanggotaan grup.',
                    ]);
                }

                $membership->role_id = $roleId;
            }

            // Maintain membership dates according to the new status.
            if ($newStatus === 'active') {
                if (in_array($oldStatus, ['pending', 'left'], true)) {
                    $membership->joined_at = $now;
                }

                // Inactive -> active preserves the previous joined_at.
                $membership->left_at = null;
            } elseif ($newStatus === 'left') {
                $membership->left_at = $now;
            } elseif ($newStatus === 'rejected') {
                $membership->joined_at = null;
                $membership->left_at = null;
            }

            $membership->status = $newStatus;
            $membership->save();

            // Append an audit record in the same transaction.
            $log = new FamilyMembershipStatusLog();

            $log->from_status = $oldStatus;
            $log->to_status = $newStatus;
            $log->changed_by = $actor?->id;
            $log->reason = $reason;
            $log->created_at = $now;

            $membership->statusLogs()->save($log);

            return $membership->refresh();
        });
    }
}
