<?php

namespace Tests\Feature\Family;

use App\Models\User;
use App\Models\Family\FamilyGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Family\FamilyPhoto;
use App\Models\Family\FamilyPhotoAlbum;

class FamilyPhotoAlbumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('family_photos');
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'test-password',
        ]);
    }

    private function createRole(
        string $name,
        string $slug,
        string $scope
    ): int {
        $role = DB::table('roles')
            ->where('slug', $slug)
            ->where('scope', $scope)
            ->first();

        if ($role) {
            return (int) $role->id;
        }

        return DB::table('roles')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'scope' => $scope,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function grantPermissions(int $roleId, array $slugs): void
    {
        foreach ($slugs as $slug) {
            $permission = DB::table('permissions')
                ->where('slug', $slug)
                ->first();

            if (! $permission) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $slug,
                    'slug' => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $permissionId = (int) $permission->id;
            }

            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createSuperAdmin(array $permissions): User
    {
        $user = $this->createUser('super-admin-photo@example.test');

        $roleId = $this->createRole(
            'Super Admin',
            'super_admin',
            'global'
        );

        $this->grantPermissions($roleId, $permissions);

        DB::table('user_roles')->insert([
            'user_id' => $user->id,
            'role_id' => $roleId,
            'role_scope' => 'global',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('family_portal_settings')->insert([
            'id' => 1,
            'super_admin_user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function createGroup(string $name, string $slug): FamilyGroup
    {
        return FamilyGroup::create([
            'name' => $name,
            'slug' => $slug,
            'group_type' => 'custom',
            'status' => 'active',
        ]);
    }
    private function createFamilyMemberWithoutPhotoPermission(
    string $email,
    FamilyGroup $group
): User {
    $user = $this->createUser($email);

    $personId = DB::table('family_persons')->insertGetId([
        'user_id' => $user->id,
        'full_name' => 'Unauthorized Photo Member',
        'life_status' => 'alive',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $roleId = $this->createRole(
        'Family Member',
        'family_member',
        'group'
    );

   DB::table('family_members')->insert([
    'family_group_id' => $group->id,
    'family_person_id' => $personId,
    'role_id' => $roleId,
    'role_scope' => 'group',
    'status' => 'active',
    'joined_at' => now(),
    'left_at' => null,
    'created_at' => now(),
    'updated_at' => now(),
]);

    return $user;
}

private function createFamilyAdminWithPermissions(
    string $email,
    FamilyGroup $group,
    array $permissions
): User {
    $user = $this->createUser($email);

    $personId = DB::table('family_persons')->insertGetId([
        'user_id' => $user->id,
        'full_name' => 'Family Admin Test',
        'life_status' => 'alive',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $roleId = $this->createRole(
        'Family Admin',
        'family_admin',
        'group'
    );

    $this->grantPermissions($roleId, $permissions);

    DB::table('family_members')->insert([
        'family_group_id' => $group->id,
        'family_person_id' => $personId,
        'role_id' => $roleId,
        'role_scope' => 'group',
        'status' => 'active',
        'joined_at' => now(),
        'left_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $user;
}

  private function createAlbumWithPhoto(User $user): array
{
    $unique = uniqid();

    $group = $this->createGroup(
        'Photo Test Family',
        'photo-test-family-' . $unique
    );

    $album = FamilyPhotoAlbum::create([
        'family_group_id' => $group->id,
        'name' => 'Album Test',
        'slug' => 'album-test',
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    $paths = [
        'originals/test/photo.jpg',
        'thumbnails/test/photo.webp',
        'previews/test/photo.webp',
    ];

    foreach ($paths as $path) {
        Storage::disk('family_photos')->put(
            $path,
            'fake-image-content'
        );
    }

    $photo = FamilyPhoto::create([
        'family_photo_album_id' => $album->id,
        'file_path' => $paths[0],
        'thumbnail_path' => $paths[1],
        'preview_path' => $paths[2],
        'original_name' => 'photo.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 100,
        'width' => 100,
        'height' => 100,
        'uploaded_by' => $user->id,
        'sort_order' => 1,
    ]);

    return compact('group', 'album', 'photo', 'paths');
}

public function test_individual_photo_delete_keeps_files_and_favorites(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.photos.delete',
    ]);

    ['album' => $album, 'photo' => $photo, 'paths' => $paths]
        = $this->createAlbumWithPhoto($user);

    $album->update([
        'cover_photo_id' => $photo->id,
    ]);

    DB::table('family_photo_favorites')->insert([
        'family_photo_id' => $photo->id,
        'user_id' => $user->id,
        'family_member_id' => null,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user, 'family')
        ->delete(
            "/family/photo-albums/{$album->id}/photos/{$photo->id}"
        );

    $response->assertRedirect();

    $this->assertSoftDeleted('family_photos', [
        'id' => $photo->id,
    ]);

    $this->assertDatabaseHas('family_photos', [
        'id' => $photo->id,
        'deleted_with_album_at' => null,
    ]);

    $this->assertDatabaseHas('family_photo_favorites', [
        'family_photo_id' => $photo->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('family_photo_albums', [
    'id' => $album->id,
    'cover_photo_id' => $photo->id,
]);

    foreach ($paths as $path) {
        Storage::disk('family_photos')->assertExists($path);
    }
}

public function test_individually_deleted_photo_can_be_restored(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.photos.delete',
    ]);

    ['album' => $album, 'photo' => $photo, 'paths' => $paths]
        = $this->createAlbumWithPhoto($user);

    $this->actingAs($user, 'family')
        ->delete(
            "/family/photo-albums/{$album->id}/photos/{$photo->id}"
        );

    $response = $this->post(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}/restore"
    );

    $response->assertRedirect(route('family.photo-albums.trash'));

    $this->assertDatabaseHas('family_photos', [
        'id' => $photo->id,
        'deleted_at' => null,
        'deleted_with_album_at' => null,
    ]);

    foreach ($paths as $path) {
        Storage::disk('family_photos')->assertExists($path);
    }
}
public function test_deleting_album_only_restores_photos_deleted_with_that_album(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    ['album' => $album, 'photo' => $photo, 'paths' => $paths]
        = $this->createAlbumWithPhoto($user);

    // Foto kedua akan dihapus secara individual lebih dahulu.
    $individualPhoto = FamilyPhoto::create([
        'family_photo_album_id' => $album->id,
        'file_path' => 'originals/test/individual.jpg',
        'thumbnail_path' => 'thumbnails/test/individual.webp',
        'preview_path' => 'previews/test/individual.webp',
        'original_name' => 'individual.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 100,
        'uploaded_by' => $user->id,
        'sort_order' => 2,
    ]);

    // Hapus foto kedua secara individual.
    $this->actingAs($user, 'family')->delete(
        "/family/photo-albums/{$album->id}/photos/{$individualPhoto->id}"
    );

    // Hapus album beserta foto pertama yang masih aktif.
    $this->delete(
        "/family/photo-albums/{$album->id}"
    );

    $album->refresh();
    $photo->refresh();
    $individualPhoto->refresh();

    $this->assertNotNull($album->deleted_at);
    $this->assertNotNull($photo->deleted_at);
    $this->assertNotNull($photo->deleted_with_album_at);

    $this->assertNotNull($individualPhoto->deleted_at);
    $this->assertNull($individualPhoto->deleted_with_album_at);

    // Restore album.
    $response = $this->post(
        "/family/photo-albums/{$album->id}/restore"
    );

    $response->assertRedirect(route('family.photo-albums.trash'));

    $this->assertDatabaseHas('family_photo_albums', [
        'id' => $album->id,
        'deleted_at' => null,
    ]);

    // Foto yang ikut terhapus karena album harus kembali aktif.
    $this->assertDatabaseHas('family_photos', [
        'id' => $photo->id,
        'deleted_at' => null,
        'deleted_with_album_at' => null,
    ]);

    // Foto yang dihapus sendiri tetap berada di Sampah.
    $this->assertSoftDeleted('family_photos', [
        'id' => $individualPhoto->id,
    ]);

    $this->assertDatabaseHas('family_photos', [
        'id' => $individualPhoto->id,
        'deleted_with_album_at' => null,
    ]);

    foreach ($paths as $path) {
        Storage::disk('family_photos')->assertExists($path);
    }
}

public function test_permanently_deleting_photo_removes_database_favorites_and_files(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.photos.delete',
    ]);

    ['album' => $album, 'photo' => $photo, 'paths' => $paths]
        = $this->createAlbumWithPhoto($user);

    DB::table('family_photo_favorites')->insert([
        'family_photo_id' => $photo->id,
        'user_id' => $user->id,
        'family_member_id' => null,
        'created_at' => now(),
    ]);

    // Masukkan foto ke Sampah terlebih dahulu.
    $this->actingAs($user, 'family')->delete(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}"
    );

    // Hapus permanen.
    $response = $this->delete(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}/force-delete"
    );

    $response->assertRedirect(route('family.photo-albums.trash'));

    $this->assertDatabaseMissing('family_photos', [
        'id' => $photo->id,
    ]);

    $this->assertDatabaseMissing('family_photo_favorites', [
        'family_photo_id' => $photo->id,
    ]);

    foreach ($paths as $path) {
        Storage::disk('family_photos')->assertMissing($path);
    }
}

public function test_permanently_deleting_album_removes_all_photos_favorites_and_files(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    ['album' => $album, 'photo' => $photo, 'paths' => $paths]
        = $this->createAlbumWithPhoto($user);

    $individualPhoto = FamilyPhoto::create([
        'family_photo_album_id' => $album->id,
        'file_path' => 'originals/test/individual.jpg',
        'thumbnail_path' => 'thumbnails/test/individual.webp',
        'preview_path' => 'previews/test/individual.webp',
        'original_name' => 'individual.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 100,
        'uploaded_by' => $user->id,
        'sort_order' => 2,
    ]);

    $individualPaths = [
        'originals/test/individual.jpg',
        'thumbnails/test/individual.webp',
        'previews/test/individual.webp',
    ];

    foreach ($individualPaths as $path) {
        Storage::disk('family_photos')->put($path, 'fake-image-content');
    }

    foreach ([$photo, $individualPhoto] as $item) {
        DB::table('family_photo_favorites')->insert([
            'family_photo_id' => $item->id,
            'user_id' => $user->id,
            'family_member_id' => null,
            'created_at' => now(),
        ]);
    }

    // Hapus foto kedua secara individual.
    $this->actingAs($user, 'family')->delete(
        "/family/photo-albums/{$album->id}/photos/{$individualPhoto->id}"
    );

    // Hapus album ke Sampah.
    $this->delete("/family/photo-albums/{$album->id}");

    // Hapus album secara permanen.
    $response = $this->delete(
        "/family/photo-albums/{$album->id}/force-delete"
    );

    $response->assertRedirect(route('family.photo-albums.trash'));

    $this->assertDatabaseMissing('family_photo_albums', [
        'id' => $album->id,
    ]);

    foreach ([$photo, $individualPhoto] as $item) {
        $this->assertDatabaseMissing('family_photos', [
            'id' => $item->id,
        ]);

        $this->assertDatabaseMissing('family_photo_favorites', [
            'family_photo_id' => $item->id,
        ]);
    }

    foreach (array_merge($paths, $individualPaths) as $path) {
        Storage::disk('family_photos')->assertMissing($path);
    }
}
public function test_empty_trash_permanently_removes_all_trashed_items(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    // Album pertama: akan dihapus sebagai album.
    ['album' => $deletedAlbum, 'photo' => $albumPhoto, 'paths' => $albumPaths]
        = $this->createAlbumWithPhoto($user);

    // Foto kedua dalam album pertama, dihapus secara individual.
    $deletedAlbumPhoto = FamilyPhoto::create([
        'family_photo_album_id' => $deletedAlbum->id,
        'file_path' => 'originals/test/deleted-album-photo.jpg',
        'thumbnail_path' => 'thumbnails/test/deleted-album-photo.webp',
        'preview_path' => 'previews/test/deleted-album-photo.webp',
        'original_name' => 'deleted-album-photo.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 100,
        'uploaded_by' => $user->id,
    ]);

    $deletedAlbumPhotoPaths = [
        'originals/test/deleted-album-photo.jpg',
        'thumbnails/test/deleted-album-photo.webp',
        'previews/test/deleted-album-photo.webp',
    ];

    foreach ($deletedAlbumPhotoPaths as $path) {
        Storage::disk('family_photos')->put($path, 'fake-image-content');
    }

    // Album kedua tetap aktif, tetapi satu fotonya masuk Sampah.
    ['album' => $activeAlbum, 'photo' => $individualPhoto, 'paths' => $individualPaths]
        = $this->createAlbumWithPhoto($user);

    // Hapus foto secara individual dari album kedua.
    $this->actingAs($user, 'family')->delete(
        "/family/photo-albums/{$activeAlbum->id}/photos/{$individualPhoto->id}"
    );

    // Hapus album pertama beserta foto aktifnya.
    $this->delete("/family/photo-albums/{$deletedAlbum->id}");

    // Pastikan ada favorit pada setiap foto.
    foreach ([$albumPhoto, $deletedAlbumPhoto, $individualPhoto] as $item) {
        DB::table('family_photo_favorites')->insert([
            'family_photo_id' => $item->id,
            'user_id' => $user->id,
            'family_member_id' => null,
            'created_at' => now(),
        ]);
    }

    // Kosongkan Sampah.
    $response = $this->delete('/family/photo-albums/trash/empty');

    $response->assertRedirect(route('family.photo-albums.trash'));

    // Album yang dihapus harus hilang permanen.
    $this->assertDatabaseMissing('family_photo_albums', [
        'id' => $deletedAlbum->id,
    ]);

    // Album kedua masih aktif.
    $this->assertDatabaseHas('family_photo_albums', [
        'id' => $activeAlbum->id,
        'deleted_at' => null,
    ]);

    // Semua foto di Sampah harus hilang.
    foreach ([$albumPhoto, $deletedAlbumPhoto, $individualPhoto] as $item) {
        $this->assertDatabaseMissing('family_photos', [
            'id' => $item->id,
        ]);

        $this->assertDatabaseMissing('family_photo_favorites', [
            'family_photo_id' => $item->id,
        ]);
    }

    // Semua file fisik harus ikut hilang.
    foreach (
        array_merge(
            $albumPaths,
            $deletedAlbumPhotoPaths,
            $individualPaths
        ) as $path
    ) {
        Storage::disk('family_photos')->assertMissing($path);
    }
}
public function test_user_without_permission_cannot_empty_trash(): void
{
    $owner = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    ['group' => $group, 'album' => $album, 'photo' => $photo, 'paths' => $paths]
        = $this->createAlbumWithPhoto($owner);

    // Hapus foto menggunakan pemilik yang memiliki permission.
    $this->actingAs($owner, 'family')->delete(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}"
    );

    // Anggota aktif grup yang sama, tetapi tanpa permission foto.
    $unauthorizedUser = $this->createFamilyMemberWithoutPhotoPermission(
        'unauthorized-photo@example.test',
        $group
    );

    $response = $this->actingAs($unauthorizedUser, 'family')
        ->delete('/family/photo-albums/trash/empty');


    // Tidak boleh mengosongkan Sampah.
    $response->assertForbidden();

    $this->assertSoftDeleted('family_photos', [
        'id' => $photo->id,
    ]);

    $this->assertDatabaseHas('family_photo_albums', [
        'id' => $album->id,
    ]);

    foreach ($paths as $path) {
        Storage::disk('family_photos')->assertExists($path);
    }
}

public function test_family_admin_can_only_see_trashed_albums_from_own_group(): void
{
    $owner = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    // Buat album milik Grup B.
    [
        'group' => $groupB,
        'album' => $albumB,
    ] = $this->createAlbumWithPhoto($owner);

    // Hapus album Grup B ke Sampah.
    $this->actingAs($owner, 'family')
        ->delete("/family/photo-albums/{$albumB->id}");

    $this->assertSoftDeleted('family_photo_albums', [
        'id' => $albumB->id,
    ]);

    // Buat Grup A.
    $groupA = $this->createGroup(
        'Group A',
        'group-a-' . uniqid()
    );

    // Anggota Grup A memiliki permission melihat Sampah.
    $userA = $this->createFamilyAdminWithPermissions(
        'admin-group-a@example.test',
        $groupA,
        [
            'photo_albums.view',
        ]
    );

    // Login sebagai anggota Grup A.
    $response = $this->actingAs($userA, 'family')
        ->get(route('family.photo-albums.trash'));

    $response->assertOk();

    // Album Grup B tidak boleh muncul.
    $response->assertViewHas('trashedAlbums', function ($albums) use ($albumB) {
        return $albums->where('id', $albumB->id)->isEmpty();
    });
}
public function test_family_admin_cannot_restore_trashed_album_from_another_group(): void
{
    $owner = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    [
        'album' => $albumB,
    ] = $this->createAlbumWithPhoto($owner);

    // Hapus album Grup B ke Sampah.
    $this->actingAs($owner, 'family')
        ->delete("/family/photo-albums/{$albumB->id}");

    $groupA = $this->createGroup(
        'Group A',
        'group-a-restore-' . uniqid()
    );

    $userA = $this->createFamilyAdminWithPermissions(
        'admin-restore-group-a@example.test',
        $groupA,
        [
            'photo_albums.view',
            'photo_albums.delete',
        ]
    );

    // Anggota Grup A mencoba memulihkan album Grup B.
    $response = $this->actingAs($userA, 'family')
        ->post(route('family.photo-albums.restore', $albumB->id));

    $response->assertNotFound();

    // Album harus tetap berada di Sampah.
    $this->assertSoftDeleted('family_photo_albums', [
        'id' => $albumB->id,
    ]);
}
public function test_family_admin_cannot_permanently_delete_album_from_another_group(): void
{
    $owner = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    [
        'album' => $albumB,
        'photo' => $photoB,
    ] = $this->createAlbumWithPhoto($owner);

    // Hapus album Grup B ke Sampah.
    $this->actingAs($owner, 'family')
        ->delete("/family/photo-albums/{$albumB->id}");

    $groupA = $this->createGroup(
        'Group A',
        'group-a-force-delete-' . uniqid()
    );

    $userA = $this->createFamilyAdminWithPermissions(
        'admin-force-delete-group-a@example.test',
        $groupA,
        [
            'photo_albums.view',
            'photo_albums.delete',
        ]
    );

    // Anggota Grup A mencoba menghapus permanen album Grup B.
    $response = $this->actingAs($userA, 'family')
        ->delete(route('family.photo-albums.force-delete', $albumB->id));

    $response->assertNotFound();

    // Album dan fotonya harus tetap ada di Sampah.
    $this->assertSoftDeleted('family_photo_albums', [
        'id' => $albumB->id,
    ]);

    $this->assertDatabaseHas('family_photos', [
        'id' => $photoB->id,
    ]);
}
public function test_empty_trash_only_deletes_items_from_accessible_groups(): void
{
    $owner = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    [
        'album' => $albumB,
        'photo' => $photoB,
        'paths' => $pathsB,
    ] = $this->createAlbumWithPhoto($owner);

    // Album Grup B masuk Sampah.
    $this->actingAs($owner, 'family')
        ->delete("/family/photo-albums/{$albumB->id}");

    $groupA = $this->createGroup(
        'Group A',
        'group-a-empty-trash-' . uniqid()
    );

    $userA = $this->createFamilyAdminWithPermissions(
        'admin-empty-trash-group-a@example.test',
        $groupA,
        [
            'photo_albums.view',
            'photo_albums.delete',
        ]
    );

    // Grup A mengosongkan Sampah.
    $response = $this->actingAs($userA, 'family')
        ->delete(route('family.photo-albums.trash.empty'));

    $response->assertRedirect(route('family.photo-albums.trash'));

    // Album dan foto Grup B tidak boleh terhapus.
    $this->assertSoftDeleted('family_photo_albums', [
        'id' => $albumB->id,
    ]);

    $this->assertDatabaseHas('family_photos', [
        'id' => $photoB->id,
    ]);

    foreach ($pathsB as $path) {
        Storage::disk('family_photos')->assertExists($path);
    }
}
public function test_family_admin_cannot_restore_photo_from_another_group(): void
{
    $owner = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    [
        'album' => $albumB,
        'photo' => $photoB,
    ] = $this->createAlbumWithPhoto($owner);

    // Hapus foto secara individual.
    $this->actingAs($owner, 'family')
        ->delete(
            "/family/photo-albums/{$albumB->id}/photos/{$photoB->id}"
        );

    $this->assertSoftDeleted('family_photos', [
        'id' => $photoB->id,
    ]);

    $groupA = $this->createGroup(
        'Group A',
        'group-a-photo-restore-' . uniqid()
    );

    $userA = $this->createFamilyAdminWithPermissions(
        'admin-photo-restore-group-a@example.test',
        $groupA,
        [
            'photo_albums.view',
            'photo_albums.delete',
        ]
    );

    $response = $this->actingAs($userA, 'family')
        ->post(
            route(
                'family.photo-albums.photos.restore',
                [$albumB->id, $photoB->id]
            )
        );

    $response->assertNotFound();

    $this->assertSoftDeleted('family_photos', [
        'id' => $photoB->id,
    ]);
}
public function test_family_admin_cannot_permanently_delete_photo_from_another_group(): void
{
    $owner = $this->createSuperAdmin([
        'photo_albums.delete',
        'photo_albums.photos.delete',
    ]);

    [
        'album' => $albumB,
        'photo' => $photoB,
        'paths' => $pathsB,
    ] = $this->createAlbumWithPhoto($owner);

    // Hapus foto secara individual ke Sampah.
    $this->actingAs($owner, 'family')
        ->delete(
            "/family/photo-albums/{$albumB->id}/photos/{$photoB->id}"
        );

    $groupA = $this->createGroup(
        'Group A',
        'group-a-photo-force-delete-' . uniqid()
    );

    $userA = $this->createFamilyAdminWithPermissions(
        'admin-photo-force-delete-group-a@example.test',
        $groupA,
        [
            'photo_albums.view',
            'photo_albums.delete',
        ]
    );

    // Anggota Grup A mencoba menghapus permanen foto Grup B.
    $response = $this->actingAs($userA, 'family')
        ->delete(
            route(
                'family.photo-albums.photos.force-delete',
                [$albumB->id, $photoB->id]
            )
        );

    $response->assertNotFound();

    // Data foto dan file fisiknya harus tetap ada.
    $this->assertSoftDeleted('family_photos', [
        'id' => $photoB->id,
    ]);

    foreach ($pathsB as $path) {
        Storage::disk('family_photos')->assertExists($path);
    }
}

public function test_photo_favorites_are_preserved_when_photo_is_deleted_and_restored(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.photos.delete',
    ]);

    ['album' => $album, 'photo' => $photo]
        = $this->createAlbumWithPhoto($user);

    DB::table('family_photo_favorites')->insert([
        'family_photo_id' => $photo->id,
        'user_id' => $user->id,
        'family_member_id' => null,
        'created_at' => now(),
    ]);

    // Pastikan favorit awal hanya satu.
    $this->assertDatabaseCount('family_photo_favorites', 1);

    // Hapus foto secara individual ke Sampah.
    $this->actingAs($user, 'family')->delete(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}"
    );

    $this->assertSoftDeleted('family_photos', [
        'id' => $photo->id,
    ]);

    // Favorit harus tetap ada ketika foto berada di Sampah.
    $this->assertDatabaseHas('family_photo_favorites', [
        'family_photo_id' => $photo->id,
        'user_id' => $user->id,
        'family_member_id' => null,
    ]);

    $this->assertDatabaseCount('family_photo_favorites', 1);

    // Pulihkan foto.
    $response = $this->post(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}/restore"
    );

    $response->assertRedirect(route('family.photo-albums.trash'));

    $this->assertDatabaseHas('family_photos', [
        'id' => $photo->id,
        'deleted_at' => null,
        'deleted_with_album_at' => null,
    ]);

    // Favorit harus tetap terhubung ke foto yang sama.
    $this->assertDatabaseHas('family_photo_favorites', [
        'family_photo_id' => $photo->id,
        'user_id' => $user->id,
        'family_member_id' => null,
    ]);

    // Tidak boleh muncul favorit duplikat.
    $this->assertDatabaseCount('family_photo_favorites', 1);
}

public function test_album_cover_is_preserved_when_cover_photo_is_deleted_and_restored(): void
{
    $user = $this->createSuperAdmin([
        'photo_albums.photos.delete',
    ]);

    ['album' => $album, 'photo' => $photo]
        = $this->createAlbumWithPhoto($user);

    // Tetapkan foto sebagai cover album.
    $album->update([
        'cover_photo_id' => $photo->id,
    ]);

    $this->assertSame(
        $photo->id,
        $album->fresh()->cover_photo_id
    );

    // Hapus foto cover ke Sampah.
    $this->actingAs($user, 'family')->delete(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}"
    );

    // Referensi cover harus tetap tersimpan.
    $this->assertDatabaseHas('family_photo_albums', [
        'id' => $album->id,
        'cover_photo_id' => $photo->id,
    ]);

    // Relasi cover tidak menampilkan foto yang sudah dihapus.
    $this->assertNull(
        $album->fresh()->coverPhoto
    );

    // Pulihkan foto.
    $response = $this->post(
        "/family/photo-albums/{$album->id}/photos/{$photo->id}/restore"
    );

    $response->assertRedirect(route('family.photo-albums.trash'));

    // Referensi cover tetap sama.
    $restoredAlbum = $album->fresh();

    $this->assertSame(
        $photo->id,
        $restoredAlbum->cover_photo_id
    );

    // Relasi cover harus kembali menunjuk ke foto semula.
    $this->assertSame(
        $photo->id,
        $restoredAlbum->coverPhoto->id
    );
}


}
