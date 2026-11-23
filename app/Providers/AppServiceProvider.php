<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Family\FamilyAccessService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(FamilyAccessService $familyAccess): void
    {
        Gate::define(
            'family.global',
            function (User $user, string $permissionSlug) use ($familyAccess): bool {
                return $familyAccess->hasGlobalPermission(
                    $user,
                    $permissionSlug
                );
            }
        );

        Gate::define(
            'family.group',
            function (
                User $user,
                int $groupId,
                string $permissionSlug
            ) use ($familyAccess): bool {
                return $familyAccess->hasGroupPermission(
                    $user,
                    $groupId,
                    $permissionSlug
                );
            }
        );
    }
}