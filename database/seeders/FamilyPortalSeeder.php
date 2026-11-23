<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamilyPortalSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            /*
            |--------------------------------------------------------------------------
            | Permission Catalog
            |--------------------------------------------------------------------------
            */

            $permissions = [
                // Dashboard
                'dashboard.view' => 'Melihat dashboard',

                // Group management
                'groups.view' => 'Melihat grup keluarga',
                'groups.create' => 'Membuat grup keluarga',
                'groups.update' => 'Memperbarui informasi dasar grup',
                'groups.activate' => 'Mengaktifkan atau menonaktifkan grup',
                'groups.archive' => 'Mengarsipkan grup keluarga',
                'groups.restore' => 'Memulihkan grup keluarga',
                'groups.type.manage' => 'Mengubah jenis grup keluarga',

                // Members
                'members.view' => 'Melihat anggota keluarga',
                'members.invite' => 'Mengundang anggota keluarga',
                'members.manage' => 'Mengelola keanggotaan grup',

                // Persons and relationships
                'persons.view' => 'Melihat data individu keluarga',
                'persons.create' => 'Membuat data individu keluarga',
                'persons.update' => 'Memperbarui data individu keluarga',
                'relationships.manage' => 'Mengelola hubungan kekerabatan',

                // Locations
                'locations.view_shared' => 'Melihat lokasi yang dibagikan',
                'locations.share_own' => 'Mengatur pembagian lokasi sendiri',
                'locations.manage_group' => 'Mengelola pengaturan lokasi grup',

                // Devices
                'devices.manage_own' => 'Mengelola perangkat sendiri',
                'devices.manage_group' => 'Mengelola perangkat anggota grup',

                // Albums
                'albums.view' => 'Melihat album yang diizinkan',
                'albums.manage' => 'Mengelola album keluarga',

                // Personal settings
                'notifications.manage_own' => 'Mengatur notifikasi sendiri',
                'settings.manage_own' => 'Mengatur preferensi akun sendiri',

                // Administration and audit
                'roles.manage' => 'Mengelola role dan hak akses sistem',
                'audit_logs.view' => 'Melihat audit log global',
                'audit_logs.view_group' => 'Melihat audit log grup sendiri',
            ];

            foreach ($permissions as $slug => $description) {
                DB::table('permissions')->updateOrInsert(
                    ['slug' => $slug],
                    [
                        'name' => $slug,
                        'description' => $description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | System Roles
            |--------------------------------------------------------------------------
            */

            $familyAdminPermissions = [
                'dashboard.view',

                'groups.view',
                'groups.update',

                'members.view',
                'members.invite',
                'members.manage',

                'persons.view',
                'persons.create',
                'persons.update',
                'relationships.manage',

                'locations.view_shared',
                'locations.share_own',
                'locations.manage_group',

                'devices.manage_own',
                'devices.manage_group',

                'albums.view',
                'albums.manage',

                'notifications.manage_own',
                'settings.manage_own',

                'audit_logs.view_group',
            ];

            $familyMemberPermissions = [
                'dashboard.view',
                'groups.view',
                'members.view',
                'persons.view',
                'locations.view_shared',
                'locations.share_own',
                'devices.manage_own',
                'albums.view',
                'notifications.manage_own',
                'settings.manage_own',
            ];

            $roles = [
                'super_admin' => [
                    'name' => 'Super Admin',
                    'scope' => 'global',
                    'description' => 'Administrator global Family Portal.',
                    'permissions' => array_keys($permissions),
                ],

                'family_admin' => [
                    'name' => 'Family Admin',
                    'scope' => 'group',
                    'description' => 'Administrator untuk grup keluarga yang ditugaskan.',
                    'permissions' => $familyAdminPermissions,
                ],

                'family_member' => [
                    'name' => 'Anggota Keluarga',
                    'scope' => 'group',
                    'description' => 'Anggota biasa dalam grup keluarga.',
                    'permissions' => $familyMemberPermissions,
                ],
            ];

            foreach ($roles as $slug => $roleData) {
                DB::table('roles')->updateOrInsert(
                    ['slug' => $slug],
                    [
                        'name' => $roleData['name'],
                        'scope' => $roleData['scope'],
                        'description' => $roleData['description'],
                        'is_system' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $roleId = DB::table('roles')
                    ->where('slug', $slug)
                    ->value('id');

           // Synchronize only permissions owned by this seeder.
            // Additional permissions from other migrations remain untouched.
            $permissionIds = DB::table('permissions')
                ->whereIn('slug', $roleData['permissions'])
                ->pluck('id');

            DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->whereIn('permission_id', $permissionIds)
                ->delete();

                foreach ($permissionIds as $permissionId) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
