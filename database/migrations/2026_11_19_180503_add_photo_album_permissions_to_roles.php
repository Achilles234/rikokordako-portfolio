
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $permissions = [
                'photo_albums.view' => 'Melihat album dan foto keluarga',
                'photo_albums.create' => 'Membuat album foto',
                'photo_albums.update' => 'Mengubah informasi album',
                'photo_albums.delete' => 'Mengarsipkan atau menghapus album',
                'photo_albums.upload' => 'Mengunggah foto ke album',
                'photo_albums.favorite' => 'Menambahkan foto ke favorit',
                'photo_albums.photos.delete' => 'Menghapus foto dari album',
            ];

            $permissionIds = [];

            foreach ($permissions as $slug => $description) {
                $permission = DB::table('permissions')
                    ->where('slug', $slug)
                    ->first();

                if (! $permission) {
                    $id = DB::table('permissions')->insertGetId([
                        'name' => $description,
                        'slug' => $slug,
                        'description' => $description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $id = $permission->id;
                }

                $permissionIds[$slug] = $id;
            }

            $rolePermissions = [
                'family_member' => [
                    'photo_albums.view',
                    'photo_albums.upload',
                    'photo_albums.favorite',
                ],
                'family_admin' => array_keys($permissions),
                'super_admin' => array_keys($permissions),
            ];

            foreach ($rolePermissions as $roleSlug => $slugs) {
                $role = DB::table('roles')
                    ->where('slug', $roleSlug)
                    ->first();

                if (! $role) {
                    continue;
                }

                foreach ($slugs as $slug) {
                    DB::table('role_permissions')->insertOrIgnore([
                        'role_id' => $role->id,
                        'permission_id' => $permissionIds[$slug],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $slugs = [
                'photo_albums.view',
                'photo_albums.create',
                'photo_albums.update',
                'photo_albums.delete',
                'photo_albums.upload',
                'photo_albums.favorite',
                'photo_albums.photos.delete',
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
        });
    }
};
