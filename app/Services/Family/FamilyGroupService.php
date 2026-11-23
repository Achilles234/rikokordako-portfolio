<?php

namespace App\Services\Family;

use App\Models\Family\FamilyGroup;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FamilyGroupService
{
    public function __construct(
        private readonly FamilyAccessService $access
    ) {}

    /**
     * Create a new family group. Super Admin only.
     */
    public function create(
        User $actor,
        array $data,
        ?string $reason = null
    ): FamilyGroup {
        $this->authorizeGlobal($actor, 'groups.create');

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'group_type' => ['sometimes', 'required', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $validated,
            $reason
        ) {
            $name = trim($validated['name']);
            $slug = $this->generateUniqueSlug($name);

            $group = FamilyGroup::create([
                'name' => $name,
                'slug' => $slug,
                'group_type' => $validated['group_type'] ?? 'custom',
                'description' => $validated['description'] ?? null,
                'status' => 'active',
                'created_by' => $actor->id,
            ]);

            $this->log(
                $group,
                $actor,
                'created',
                null,
                [
                    'name' => $group->name,
                    'slug' => $group->slug,
                    'group_type' => $group->group_type,
                    'description' => $group->description,
                    'status' => $group->status,
                ],
                $reason
            );

            return $group->refresh();
        });
    }

    /**
     * Update group name and description.
     * Super Admin or authorized Family Admin of this group.
     */
    public function updateDetails(
        User $actor,
        int $groupId,
        array $data,
        ?string $reason = null
    ): FamilyGroup {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ])->validate();

        if ($validated === []) {
            throw ValidationException::withMessages([
                'data' => 'Tidak ada perubahan data grup.',
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $groupId,
            $validated,
            $reason
        ) {
            $group = $this->lockedGroup($groupId);

            $this->authorizeGroup($actor, $groupId, 'groups.update');

            if ($group->status === 'archived') {
                throw ValidationException::withMessages([
                    'group' => 'Grup yang diarsipkan tidak dapat diedit.',
                ]);
            }

            $oldValues = [];
            $newValues = [];

            foreach ($validated as $field => $value) {
                $value = $field === 'name' ? trim($value) : $value;

                if ($group->{$field} !== $value) {
                    $oldValues[$field] = $group->{$field};
                    $newValues[$field] = $value;
                    $group->{$field} = $value;
                }
            }

            if ($newValues !== []) {
                $group->save();

                $this->log(
                    $group,
                    $actor,
                    'details_updated',
                    $oldValues,
                    $newValues,
                    $reason
                );
            }

            return $group->refresh();
        });
    }

    /**
     * Change group type. Super Admin only.
     */
    public function changeType(
        User $actor,
        int $groupId,
        string $groupType,
        ?string $reason = null
    ): FamilyGroup {
        Validator::make(
            ['group_type' => $groupType],
            ['group_type' => ['required', 'string', 'max:30']]
        )->validate();

        return DB::transaction(function () use (
            $actor,
            $groupId,
            $groupType,
            $reason
        ) {
            $group = $this->lockedGroup($groupId);

            $this->authorizeGlobal($actor, 'groups.type.manage');

            if ($group->status === 'archived') {
                throw ValidationException::withMessages([
                    'group' => 'Tipe grup yang diarsipkan tidak dapat diubah.',
                ]);
            }

            if ($group->group_type !== $groupType) {
                $old = ['group_type' => $group->group_type];
                $new = ['group_type' => $groupType];

                $group->group_type = $groupType;
                $group->save();

                $this->log(
                    $group,
                    $actor,
                    'type_changed',
                    $old,
                    $new,
                    $reason
                );
            }

            return $group->refresh();
        });
    }

    /**
     * Activate or deactivate a group. Super Admin only.
     */
    public function changeStatus(
        User $actor,
        int $groupId,
        string $status,
        ?string $reason = null
    ): FamilyGroup {
        Validator::make(
            ['status' => $status],
            ['status' => ['required', 'in:active,inactive']]
        )->validate();

        return DB::transaction(function () use (
            $actor,
            $groupId,
            $status,
            $reason
        ) {
            $group = $this->lockedGroup($groupId);

            $this->authorizeGlobal($actor, 'groups.activate');

            if ($group->status === 'archived') {
                throw ValidationException::withMessages([
                    'group' => 'Grup yang diarsipkan harus dipulihkan terlebih dahulu.',
                ]);
            }

            if ($group->status !== $status) {
                $old = ['status' => $group->status];
                $new = ['status' => $status];

                $group->status = $status;
                $group->save();

                $this->log(
                    $group,
                    $actor,
                    $status === 'active' ? 'activated' : 'deactivated',
                    $old,
                    $new,
                    $reason
                );
            }

            return $group->refresh();
        });
    }

    /**
     * Archive a group. Super Admin only.
     */
    public function archive(
        User $actor,
        int $groupId,
        ?string $reason = null
    ): FamilyGroup {
        return DB::transaction(function () use (
            $actor,
            $groupId,
            $reason
        ) {
            $group = $this->lockedGroup($groupId);

            $this->authorizeGlobal($actor, 'groups.archive');

            if ($group->status === 'archived') {
                throw ValidationException::withMessages([
                    'group' => 'Grup sudah diarsipkan.',
                ]);
            }

            $old = [
                'status' => $group->status,
                'archived_at' => null,
            ];

            $group->status = 'archived';
            $group->archived_at = now();
            $group->save();

            $this->log(
                $group,
                $actor,
                'archived',
                $old,
                [
                    'status' => 'archived',
                    'archived_at' => $group->archived_at->toISOString(),
                ],
                $reason
            );

            return $group->refresh();
        });
    }

    /**
     * Restore an archived group to active status.
     * Super Admin only.
     */
    public function restore(
        User $actor,
        int $groupId,
        ?string $reason = null
    ): FamilyGroup {
        return DB::transaction(function () use (
            $actor,
            $groupId,
            $reason
        ) {
            $group = $this->lockedGroup($groupId);

            $this->authorizeGlobal($actor, 'groups.restore');

            if ($group->status !== 'archived') {
                throw ValidationException::withMessages([
                    'group' => 'Hanya grup yang diarsipkan yang dapat dipulihkan.',
                ]);
            }

            $old = [
                'status' => 'archived',
                'archived_at' => $group->archived_at?->toISOString(),
            ];

            $group->status = 'active';
            $group->archived_at = null;
            $group->save();

            $this->log(
                $group,
                $actor,
                'restored',
                $old,
                [
                    'status' => 'active',
                    'archived_at' => null,
                ],
                $reason
            );

            return $group->refresh();
        });
    }

    private function lockedGroup(int $groupId): FamilyGroup
    {
        return FamilyGroup::query()
            ->whereKey($groupId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function authorizeGlobal(
        User $actor,
        string $permission
    ): void {
        if (! $this->access->hasGlobalPermission($actor, $permission)) {
            throw new AuthorizationException(
                'Kamu tidak memiliki izin untuk melakukan tindakan ini.'
            );
        }
    }

    private function authorizeGroup(
        User $actor,
        int $groupId,
        string $permission
    ): void {
        if (! $this->access->hasGroupPermission(
            $actor,
            $groupId,
            $permission
        )) {
            throw new AuthorizationException(
                'Kamu tidak memiliki izin untuk mengelola grup ini.'
            );
        }
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'family-group';
        }

        $slug = $base;
        $suffix = 1;

        while (
            FamilyGroup::query()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function log(
        FamilyGroup $group,
        User $actor,
        string $action,
        ?array $oldValues,
        ?array $newValues,
        ?string $reason
    ): void {
        $log = $group->activityLogs()->make([
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reason' => $reason,
        ]);

        $log->changed_by = $actor->id;
        $log->save();
    }
}
