<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissionDefinitions = [
            'agendas.view' => 'Melihat agenda keluarga',
            'agendas.create' => 'Membuat agenda keluarga',
            'agendas.update' => 'Mengubah agenda keluarga',
            'agendas.cancel' => 'Membatalkan agenda keluarga',
            'agendas.respond' => 'Mengonfirmasi kehadiran agenda',
        ];

        $permissionIds = [];

        foreach ($permissionDefinitions as $slug => $description) {
            $existing = DB::table('permissions')
                ->where('slug', $slug)
                ->first();

            if ($existing) {
                $permissionIds[$slug] = $existing->id;
                continue;
            }

            $permissionIds[$slug] = DB::table('permissions')
                ->insertGetId([
                    'name' => $slug,
                    'slug' => $slug,
                    'description' => $description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        $rolePermissions = [
            'family_member' => [
                'agendas.view',
                'agendas.respond',
            ],
            'family_admin' => [
                'agendas.view',
                'agendas.create',
                'agendas.update',
                'agendas.cancel',
                'agendas.respond',
            ],
            'super_admin' => [
                'agendas.view',
                'agendas.create',
                'agendas.update',
                'agendas.cancel',
                'agendas.respond',
            ],
        ];

        foreach ($rolePermissions as $roleSlug => $permissions) {
            $role = DB::table('roles')
                ->where('slug', $roleSlug)
                ->where('scope', $roleSlug === 'super_admin' ? 'global' : 'group')
                ->first();

            if (! $role) {
                throw new RuntimeException(
                    "Role {$roleSlug} tidak ditemukan."
                );
            }

            foreach ($permissions as $permissionSlug) {
                $permissionId = $permissionIds[$permissionSlug];

                $exists = DB::table('role_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $exists) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $slugs = [
            'agendas.view',
            'agendas.create',
            'agendas.update',
            'agendas.cancel',
            'agendas.respond',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('slug', $slugs)
            ->pluck('id');

        DB::table('role_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->delete();
    }
};
