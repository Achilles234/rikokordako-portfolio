<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\Family\FamilyGroup;
use App\Models\Family\FamilyPhotoAlbum;
use App\Models\User;
use App\Services\Family\FamilyAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Family\FamilyPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Family\FamilyPhotoFavorite;

class FamilyPhotoAlbumController extends Controller
{
    public function __construct(
        protected FamilyAccessService $familyAccess
    ) {
    }

   /**
 * Menampilkan daftar album dari grup yang dapat diakses.
 */
public function index(Request $request)
{
    $user = $request->user('family');

    abort_unless($user, 401);

    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.view'
    );

    $groupIds = $groups->pluck('id');

    $albums = FamilyPhotoAlbum::query()
        ->with(['familyGroup', 'coverPhoto'])
        ->withCount('photos')
        ->whereIn('family_group_id', $groupIds)
        ->where('status', 'active')
        ->when($request->filled('group_id'), function (Builder $query) use ($request, $groupIds) {
            $groupId = (int) $request->input('group_id');

            if (! $groupIds->contains($groupId)) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->where('family_group_id', $groupId);
        })
        ->when($request->filled('q'), function (Builder $query) use ($request) {
            $search = trim($request->input('q'));

            $query->where('name', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(12)
        ->withQueryString();

    return view('family.photo-albums.index', compact(
        'albums',
        'groups'
    ));
}

    /**
     * Form pembuatan album.
     */
    public function create(Request $request)
    {
        $user = $request->user('family');

        abort_unless($user, 401);

        $groups = $this->accessibleGroups(
            $user,
            'photo_albums.create'
        );

        abort_if($groups->isEmpty(), 403);

        $selectedGroupId = $request->integer('group_id');

        if (! $groups->contains('id', $selectedGroupId)) {
            $selectedGroupId = $groups->first()->id;
        }

        return view('family.photo-albums.create', compact(
            'groups',
            'selectedGroupId'
        ));
    }

    /**
     * Menyimpan album baru.
     */
    public function store(Request $request)
    {
        $user = $request->user('family');

        abort_unless($user, 401);

        $validated = $request->validate([
            'family_group_id' => [
                'required',
                'integer',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'event_date' => [
                'nullable',
                'date',
            ],
        ]);

        $groups = $this->accessibleGroups(
            $user,
            'photo_albums.create'
        );

        $group = $groups->firstWhere(
            'id',
            (int) $validated['family_group_id']
        );

        if (! $group) {
            throw ValidationException::withMessages([
                'family_group_id' => 'Anda tidak memiliki izin membuat album pada grup tersebut.',
            ]);
        }

        $baseSlug = Str::slug($validated['name']);

        if ($baseSlug === '') {
            $baseSlug = 'album';
        }

        $album = DB::transaction(function () use (
            $validated,
            $user,
            $group,
            $baseSlug
        ) {
            $slug = $baseSlug;
            $counter = 1;

            while (
                FamilyPhotoAlbum::withTrashed()
                    ->where('family_group_id', $group->id)
                    ->where('slug', $slug)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            return FamilyPhotoAlbum::create([
                'family_group_id' => $group->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'event_date' => $validated['event_date'] ?? null,
                'created_by' => $user->id,
                'status' => 'active',
            ]);
        });

        return redirect()
            ->route('family.photo-albums.index')
            ->with('success', 'Album foto berhasil dibuat.');
    }

    /**
     * Mengambil grup yang boleh diakses berdasarkan membership
     * dan permission pengguna.
     */
    private function accessibleGroups(
        User $user,
        string $permission
    ): Collection {
        $query = FamilyGroup::query()
            ->where('status', 'active');

        if (! $this->familyAccess->isSuperAdmin($user)) {
            $memberGroupIds = DB::table('family_members')
                ->join(
                    'family_persons',
                    'family_persons.id',
                    '=',
                    'family_members.family_person_id'
                )
                ->where('family_persons.user_id', $user->id)
                ->where('family_members.status', 'active')
                ->whereNull('family_members.left_at')
                ->pluck('family_members.family_group_id');

            $query->whereIn('id', $memberGroupIds);
        }

        return $query
            ->orderBy('name')
            ->get()
            ->filter(function (FamilyGroup $group) use ($user, $permission) {
                return $this->familyAccess->hasPermission(
                    $user,
                    $permission,
                    $group->id
                );
            })
            ->values();
    }

public function destroy(int $albumId)
{
    $user = request()->user('family');

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.delete'
    )->pluck('id');

    $album = FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groupIds)
        ->whereKey($albumId)
        ->firstOrFail();

    DB::transaction(function () use ($album) {
        $deletedAt = now();

        // Hanya foto yang masih aktif ikut terhapus bersama album.
        foreach ($album->photos as $photo) {
            $photo->deleted_with_album_at = $deletedAt;
            $photo->save();

            $photo->delete();
        }

        $album->delete();
    });

    return redirect()
        ->route('family.photo-albums.index')
        ->with('success', 'Album dipindahkan ke Sampah.');
}


public function destroyPhoto(Request $request, int $albumId, int $photoId)
{
    $user = $request->user('family');

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.photos.delete'
    )->pluck('id');
    abort_if($groupIds->isEmpty(), 403, 'Anda tidak memiliki izin untuk mengosongkan Sampah.');
    $album = FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groupIds)
        ->whereKey($albumId)
        ->firstOrFail();

    $photo = $album->photos()
        ->whereKey($photoId)
        ->firstOrFail();

   DB::transaction(function () use ($photo) {
    // Referensi cover dipertahankan agar bisa kembali saat restore.
    $photo->deleted_with_album_at = null;
    $photo->save();

    $photo->delete();
});

    return back()->with(
        'success',
        'Foto dipindahkan ke Sampah.'
    );
}


public function destroyPhotos(Request $request, int $albumId)
{
    $user = $request->user('family');

    abort_unless($user, 403);

    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.photos.delete'
    );

    $album = \App\Models\Family\FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groups->modelKeys())
        ->where('status', 'active')
        ->findOrFail($albumId);

    $validated = $request->validate([
        'photo_ids' => ['required', 'array', 'min:1', 'max:100'],
        'photo_ids.*' => ['required', 'integer', 'distinct'],
    ]);

    $photoIds = array_map('intval', $validated['photo_ids']);

    $photos = $album->photos()
        ->whereIn('id', $photoIds)
        ->get();

    abort_if($photos->count() !== count($photoIds), 404);

   DB::transaction(function () use ($photos) {
    foreach ($photos as $photo) {
        $photo->deleted_with_album_at = null;
        $photo->save();

        $photo->delete();
    }
});

    return back()->with(
        'success',
        $photos->count() . ' foto berhasil dihapus.'
    );
}



public function trash(Request $request)
{
    $user = $request->user('family');

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.view'
    )->pluck('id');

    // Album yang sudah masuk Sampah.
    $trashedAlbums = FamilyPhotoAlbum::onlyTrashed()
        ->whereIn('family_group_id', $groupIds)
        ->with([
            'familyGroup',
            'creator',
            'coverPhoto' => function ($query) {
                $query->withTrashed();
            },
            'photos' => function ($query) {
                $query->withTrashed()
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ])
        ->orderByDesc('deleted_at')
        ->get();

    // Foto yang dihapus sendiri dari album yang masih aktif.
    $trashedPhotos = FamilyPhoto::onlyTrashed()
        ->whereNull('deleted_with_album_at')
        ->whereHas('album', function ($query) use ($groupIds) {
            $query->whereIn('family_group_id', $groupIds);
        })
        ->with(['album', 'uploader'])
        ->orderByDesc('deleted_at')
        ->get();

    return view('family.photo-albums.trash', compact(
        'trashedAlbums',
        'trashedPhotos'
    ));
}

public function restoreAlbum(Request $request, int $albumId)
{
    $user = $request->user('family');

    abort_unless($user, 401);

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.delete'
    )->pluck('id');

    $album = FamilyPhotoAlbum::onlyTrashed()
        ->whereIn('family_group_id', $groupIds)
        ->whereKey($albumId)
        ->firstOrFail();

    DB::transaction(function () use ($album) {
        $albumDeletedAt = $album->deleted_at;

        $photos = FamilyPhoto::onlyTrashed()
            ->where('family_photo_album_id', $album->id)
            ->whereNotNull('deleted_with_album_at')
            ->whereBetween('deleted_with_album_at', [
                $albumDeletedAt->copy()->subSeconds(2),
                $albumDeletedAt->copy()->addSeconds(2),
            ])
            ->get();

        $album->restore();

        foreach ($photos as $photo) {
            $photo->restore();

            $photo->deleted_with_album_at = null;
            $photo->save();
        }
    });

    return redirect()
        ->route('family.photo-albums.trash')
        ->with(
            'success',
            'Album dan foto yang ikut terhapus berhasil dipulihkan.'
        );
}

public function restorePhoto(
    Request $request,
    int $albumId,
    int $photoId
) {
    $user = $request->user('family');

    abort_unless($user, 401);

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.photos.delete'
    )->pluck('id');

    $album = FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groupIds)
        ->whereKey($albumId)
        ->whereNull('deleted_at')
        ->firstOrFail();

    $photo = FamilyPhoto::onlyTrashed()
        ->where('family_photo_album_id', $album->id)
        ->whereKey($photoId)
        ->whereNull('deleted_with_album_at')
        ->firstOrFail();

    $photo->restore();

    return redirect()
        ->route('family.photo-albums.trash')
        ->with('success', 'Foto berhasil dipulihkan ke album asal.');
}


public function forceDeletePhoto(
    Request $request,
    int $albumId,
    int $photoId
) {
    $user = $request->user('family');

    abort_unless($user, 401);

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.photos.delete'
    )->pluck('id');

    $album = FamilyPhotoAlbum::withTrashed()
        ->whereIn('family_group_id', $groupIds)
        ->whereKey($albumId)
        ->firstOrFail();

    $photo = FamilyPhoto::onlyTrashed()
        ->where('family_photo_album_id', $album->id)
        ->whereKey($photoId)
        ->firstOrFail();

    DB::transaction(function () use ($album, $photo) {
        if ((int) $album->cover_photo_id === (int) $photo->id) {
            $album->cover_photo_id = null;
            $album->save();
        }

        // Menghapus permanen juga menghapus favorit melalui FK cascade.
        $photo->forceDelete();
    });

    $this->deletePhotoFiles(collect([$photo]));

    return redirect()
        ->route('family.photo-albums.trash')
        ->with('success', 'Foto berhasil dihapus permanen.');
}


public function forceDeleteAlbum(Request $request, int $albumId)
{
    $user = $request->user('family');

    abort_unless($user, 401);

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.delete'
    )->pluck('id');

    $album = FamilyPhotoAlbum::onlyTrashed()
        ->whereIn('family_group_id', $groupIds)
        ->whereKey($albumId)
        ->firstOrFail();

    $photos = FamilyPhoto::withTrashed()
        ->where('family_photo_album_id', $album->id)
        ->get();

    DB::transaction(function () use ($album, $photos) {
        // Hapus foto terlebih dahulu karena FK album membatasi penghapusan.
        foreach ($photos as $photo) {
            $photo->forceDelete();
        }

        $album->forceDelete();
    });

    $this->deletePhotoFiles($photos);

    return redirect()
        ->route('family.photo-albums.trash')
        ->with('success', 'Album dan seluruh fotonya berhasil dihapus permanen.');
}


public function emptyTrash(Request $request)
{
    $user = $request->user('family');

    abort_unless($user, 401);

    $groupIds = $this->accessibleGroups(
        $user,
        'photo_albums.delete'
    )->pluck('id');
        abort_if(
        $groupIds->isEmpty(),
        403,
        'Anda tidak memiliki izin untuk mengosongkan Sampah.'
    );

    $albums = FamilyPhotoAlbum::onlyTrashed()
        ->whereIn('family_group_id', $groupIds)
        ->get();

    $trashedAlbumIds = $albums->modelKeys();

    // Semua foto dari album yang sudah dihapus,
    // termasuk foto yang mungkin masih aktif.
    $photosFromDeletedAlbums = FamilyPhoto::withTrashed()
        ->whereIn('family_photo_album_id', $trashedAlbumIds)
        ->get();

    // Foto yang dihapus sendiri dari album yang masih aktif.
    $photosFromActiveAlbums = FamilyPhoto::onlyTrashed()
        ->whereHas('album', function ($query) use ($groupIds) {
            $query->whereIn('family_group_id', $groupIds);
        })
        ->get();

    $photos = $photosFromDeletedAlbums
        ->concat($photosFromActiveAlbums)
        ->unique('id')
        ->values();

    DB::transaction(function () use ($albums, $photos) {
        foreach ($photos as $photo) {
            $photo->forceDelete();
        }

        foreach ($albums as $album) {
            $album->forceDelete();
        }
    });

    $this->deletePhotoFiles($photos);

    return redirect()
        ->route('family.photo-albums.trash')
        ->with('success', 'Sampah berhasil dikosongkan.');
}

public function setCover(Request $request, int $albumId, int $photoId)
{
    $user = $request->user('family');

    abort_unless($user, 403);

    $groups = $this->accessibleGroups($user, 'photo_albums.update');

    $album = FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groups->pluck('id'))
        ->whereKey($albumId)
        ->where('status', 'active')
        ->firstOrFail();

    $photo = FamilyPhoto::query()
        ->where('family_photo_album_id', $album->id)
        ->whereKey($photoId)
        ->whereNull('deleted_at')
        ->firstOrFail();

    DB::transaction(function () use ($album, $photo) {
        $album->update([
            'cover_photo_id' => $photo->id,
        ]);
    });

    return redirect()
        ->route('family.photo-albums.show', $album->id)
        ->with('success', 'Cover album berhasil diperbarui.');
}

public function favorites(Request $request)
{
    $user = $request->user('family');

    abort_unless($user, 403);

    $access = app(FamilyAccessService::class);

    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.favorite'
    );

    $groupIds = $groups->pluck('id');

    $familyMemberIds = DB::table('family_members')
        ->join(
            'family_persons',
            'family_persons.id',
            '=',
            'family_members.family_person_id'
        )
        ->where('family_persons.user_id', $user->id)
        ->where('family_members.status', 'active')
        ->whereNull('family_members.left_at')
        ->whereIn('family_members.family_group_id', $groupIds)
        ->pluck('family_members.id');

    $isSuperAdmin = $access->isSuperAdmin($user);

    abort_if(
        $familyMemberIds->isEmpty() && ! $isSuperAdmin,
        403
    );

    $photos = DB::table('family_photo_favorites as favorites')
        ->join(
            'family_photos as photos',
            'photos.id',
            '=',
            'favorites.family_photo_id'
        )
        ->join(
            'family_photo_albums as albums',
            'albums.id',
            '=',
            'photos.family_photo_album_id'
        )
        ->join(
            'family_groups as groups',
            'groups.id',
            '=',
            'albums.family_group_id'
        )
        ->where(function ($query) use (
            $familyMemberIds,
            $isSuperAdmin,
            $user
        ) {
            if ($familyMemberIds->isNotEmpty()) {
                $query->whereIn(
                    'favorites.family_member_id',
                    $familyMemberIds
                );
            }

            if ($isSuperAdmin) {
                $method = $familyMemberIds->isNotEmpty()
                    ? 'orWhere'
                    : 'where';

                $query->{$method}(
                    'favorites.user_id',
                    $user->id
                );
            }
        })
        ->whereIn('albums.family_group_id', $groupIds)
        ->whereNull('photos.deleted_at')
        ->whereNull('albums.deleted_at')
        ->where('albums.status', 'active')
        ->select([
            'photos.*',
            'albums.name as album_name',
            'groups.name as group_name',
        ])
        ->orderByDesc('favorites.created_at')
        ->paginate(30);

    return view(
        'family.photo-albums.favorites',
        compact('photos')
    );
}

public function toggleFavorite(Request $request, int $albumId, int $photoId)
{
    $user = $request->user('family');

    abort_unless($user, 403);

    $access = app(FamilyAccessService::class);

    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.favorite'
    );

    $album = FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groups->pluck('id'))
        ->whereKey($albumId)
        ->where('status', 'active')
        ->firstOrFail();

    $photo = FamilyPhoto::query()
        ->where('family_photo_album_id', $album->id)
        ->whereKey($photoId)
        ->whereNull('deleted_at')
        ->firstOrFail();

    $familyMemberId = DB::table('family_members')
        ->join(
            'family_persons',
            'family_persons.id',
            '=',
            'family_members.family_person_id'
        )
        ->where('family_members.family_group_id', $album->family_group_id)
        ->where('family_persons.user_id', $user->id)
        ->where('family_members.status', 'active')
        ->whereNull('family_members.left_at')
        ->value('family_members.id');

    if ($familyMemberId) {
        $identityColumn = 'family_member_id';
        $identityValue = $familyMemberId;
    } else {
        abort_unless($access->isSuperAdmin($user), 403);

        $identityColumn = 'user_id';
        $identityValue = $user->id;
    }

    $favorite = FamilyPhotoFavorite::query()
        ->where('family_photo_id', $photo->id)
        ->where($identityColumn, $identityValue)
        ->first();

    if ($favorite) {
        $favorite->delete();
        $favorited = false;
    } else {
        FamilyPhotoFavorite::create([
            'family_photo_id' => $photo->id,
            'family_member_id' => $identityColumn === 'family_member_id'
                ? $identityValue
                : null,
            'user_id' => $identityColumn === 'user_id'
                ? $identityValue
                : null,
            'created_at' => now(),
        ]);

        $favorited = true;
    }

    $favoritesCount = FamilyPhotoFavorite::query()
        ->where('family_photo_id', $photo->id)
        ->count();

    return response()->json([
        'success' => true,
        'favorited' => $favorited,
        'favorites_count' => $favoritesCount,
    ]);
}


public function updatePhoto(Request $request, int $albumId, int $photoId)
{
    $user = $request->user('family');

    abort_unless($user, 403);

    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.update'
    );

    $album = FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groups->pluck('id'))
        ->whereKey($albumId)
        ->where('status', 'active')
        ->firstOrFail();

    $photo = FamilyPhoto::query()
        ->where('family_photo_album_id', $album->id)
        ->whereKey($photoId)
        ->whereNull('deleted_at')
        ->firstOrFail();

    $validated = $request->validate([
    'caption' => ['nullable', 'string', 'max:2000'],
    'taken_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
    ]);

    $photo->update([
        'caption' => $validated['caption'] ?? null,
        'taken_at' => $validated['taken_at'] ?? null,
    ]);

    return back()->with(
        'success',
        'Informasi foto berhasil diperbarui.'
    );
}


public function show(int $albumId)
{
    $user = request()->user('family');

    abort_unless($user, 401);

    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.view'
    );

    $album = FamilyPhotoAlbum::query()
        ->whereIn('family_group_id', $groups->pluck('id'))
        ->where('status', 'active')
        ->with(['familyGroup', 'coverPhoto'])
        ->withCount('photos')
        ->findOrFail($albumId);

    // Gunakan cover yang sudah ditentukan jika masih valid.
    $coverPhoto = $album->coverPhoto;

    // Jika cover kosong, terhapus, atau bukan milik album ini,
    // ambil foto pertama dari seluruh isi album.
    if (
        ! $coverPhoto ||
        (int) $coverPhoto->family_photo_album_id !== (int) $album->id
    ) {
        $coverPhoto = $album->photos()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->first();
    }

   $filters = request()->validate([
    'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
    'date_from' => ['nullable', 'date_format:Y-m-d'],
    'date_to' => ['nullable', 'date_format:Y-m-d'],
    'search' => ['nullable', 'string', 'max:100'],
]);

$photoQuery = $album->photos();

if (! empty($filters['year'])) {
    $photoQuery->whereYear('taken_at', $filters['year']);
}

if (! empty($filters['date_from'])) {
    $photoQuery->whereDate('taken_at', '>=', $filters['date_from']);
}

if (! empty($filters['date_to'])) {
    $photoQuery->whereDate('taken_at', '<=', $filters['date_to']);
}

if (! empty($filters['search'])) {
    $photoQuery->where('caption', 'like', '%' . $filters['search'] . '%');
}

$photos = $photoQuery
    ->orderBy('sort_order')
    ->latest()
    ->paginate(30)
    ->withQueryString();

$years = $album->photos()
    ->whereNotNull('taken_at')
    ->selectRaw('YEAR(taken_at) as year')
    ->distinct()
    ->orderByDesc('year')
    ->pluck('year');

    return view('family.photo-albums.show', compact(
    'album',
    'photos',
    'coverPhoto',
    'years',
    'filters'
));
}

public function upload(int $albumId)
{
    $user = request()->user('family');
    abort_unless($user, 401);
    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.upload'
    );

   $album = FamilyPhotoAlbum::query()
    ->whereIn('family_group_id', $groups->pluck('id'))
    ->where('status', 'active')
    ->findOrFail($albumId);

    $validated = request()->validate([
        'photos' => ['required', 'array', 'min:1', 'max:20'],
        'photos.*' => [
            'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:15360',
        ],
    ], [
        'photos.required' => 'Pilih minimal satu foto.',
        'photos.max' => 'Maksimal 20 foto dalam satu kali upload.',
        'photos.*.image' => 'File harus berupa gambar.',
        'photos.*.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        'photos.*.max' => 'Ukuran setiap foto maksimal 15 MB.',
    ]);

    $disk = Storage::disk('family_photos');
    $storedPaths = [];

    try {
        DB::transaction(function () use (
            $validated,
            $album,
            $user,
            $disk,
            &$storedPaths
        ) {
            foreach ($validated['photos'] as $index => $file) {
                $dimensions = getimagesize($file->getRealPath());
                if (
                    $dimensions === false ||
                    ($dimensions[0] * $dimensions[1]) > 40000000
                ) {
                    throw new \RuntimeException(
                        'Resolusi foto terlalu besar atau file tidak valid.'
                    );
                }
                $uuid = (string) Str::uuid();
                $extension = strtolower($file->getClientOriginalExtension());

                $basePath = "{$album->family_group_id}/{$album->id}";

                $originalPath = "originals/{$basePath}/{$uuid}.{$extension}";
                $thumbnailPath = "thumbnails/{$basePath}/{$uuid}.webp";
                $previewPath = "previews/{$basePath}/{$uuid}.webp";

                $storedOriginal = $file->storeAs(
                    "originals/{$basePath}",
                    "{$uuid}.{$extension}",
                    'family_photos'
                );

                if (!$storedOriginal) {
                    throw new \RuntimeException('Gagal menyimpan foto asli.');
                }

              $storedPaths[] = $storedOriginal;


             // Thumbnail 480px
                $storedPaths[] = $thumbnailPath;

                $this->createWebpVariant(
                    $file->getRealPath(),
                    $thumbnailPath,
                    480,
                    80
                );

                // Preview 1200px
                $storedPaths[] = $previewPath;

                $this->createWebpVariant(
                    $file->getRealPath(),
                    $previewPath,
                    1200,
                    84
                );


                FamilyPhoto::create([
                    'family_photo_album_id' => $album->id,
                    'file_path' => $originalPath,
                    'thumbnail_path' => $thumbnailPath,
                    'preview_path' => $previewPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'width' => $dimensions[0] ?? null,
                    'height' => $dimensions[1] ?? null,
                    'caption' => null,
                    'taken_at' => null,
                    'uploaded_by' => $user->id,
                    'sort_order' => $index,
                ]);
            }
        });
  } catch (\Throwable $e) {
        // Catat penyebab utama kegagalan upload.
        report($e);

        // Bersihkan seluruh file yang sempat dibuat.
        try {
            if (! empty($storedPaths)) {
                $deleted = $disk->delete($storedPaths);

                if (! $deleted) {
                    report(new \RuntimeException(
                        'Sebagian file upload gagal dibersihkan dari storage.'
                    ));
                }
            }
        } catch (\Throwable $cleanupException) {
            // Jangan sampai error cleanup menutupi error upload utama.
            report($cleanupException);
        }

        return back()->withErrors([
            'photos' => 'Upload foto gagal. Silakan coba kembali.',
        ]);
    }

    return redirect()
        ->route('family.photo-albums.show', $album->id)
        ->with('success', 'Foto berhasil diunggah ke album.');
}


/**
 * Menampilkan foto privat setelah memeriksa hak akses pengguna.
 */

public function photo(
    Request $request,
    int $albumId,
    int $photoId,
    string $variant = 'thumbnail'
) {
    $user = $request->user('family');

    abort_unless($user, 401);

    $groups = $this->accessibleGroups(
        $user,
        'photo_albums.view'
    );

    // Album aktif atau album yang sudah masuk Sampah.
    $album = FamilyPhotoAlbum::withTrashed()
        ->whereIn('family_group_id', $groups->pluck('id'))
        ->whereKey($albumId)
        ->firstOrFail();

    // Foto aktif atau foto yang sudah masuk Sampah.
    $photo = FamilyPhoto::withTrashed()
        ->where('family_photo_album_id', $album->id)
        ->whereKey($photoId)
        ->firstOrFail();

    // Foto terhapus hanya boleh ditampilkan jika:
    // 1. Album ikut terhapus, atau
    // 2. Foto dihapus secara individual.
    //
    // Foto aktif dalam album terhapus tidak boleh muncul.
    if ($album->trashed() && ! $photo->trashed()) {
        abort(404);
    }

    // Foto terhapus dari album aktif hanya dapat ditampilkan
    // melalui konteks Sampah.
    if ($photo->trashed() && ! $request->routeIs(
        'family.photo-albums.trash'
    )) {
        abort(404);
    }

    // Tentukan varian gambar.
    $path = match ($variant) {
        'thumbnail' => $photo->thumbnail_path,
        'preview' => $photo->preview_path ?: $photo->thumbnail_path,
        default => abort(404),
    };

    $disk = Storage::disk('family_photos');

    abort_unless($path && $disk->exists($path), 404);

    return response()->file(
        $disk->path($path),
        [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]
    );
}


private function deletePhotoFiles(Collection $photos): void
{
    $paths = $photos
        ->flatMap(function (FamilyPhoto $photo) {
            return [
                $photo->file_path,
                $photo->thumbnail_path,
                $photo->preview_path,
            ];
        })
        ->filter()
        ->unique()
        ->values()
        ->all();

    if (empty($paths)) {
        return;
    }

    try {
        $disk = Storage::disk('family_photos');

        foreach (array_chunk($paths, 100) as $chunk) {
            $disk->delete($chunk);
        }
    } catch (\Throwable $e) {
        // Database sudah terhapus. Catat agar file yatim
        // dapat dibersihkan melalui proses maintenance.
        report($e);
    }
}


private function createWebpVariant(
    string $sourcePath,
    string $targetPath,
    int $maxEdge,
    int $quality = 82
): void {
    if (! function_exists('imagewebp')) {
        throw new \RuntimeException(
            'Dukungan WebP pada GD tidak tersedia.'
        );
    }

    $imageData = file_get_contents($sourcePath);

    if ($imageData === false) {
        throw new \RuntimeException(
            'File gambar sumber tidak dapat dibaca.'
        );
    }

    $source = imagecreatefromstring($imageData);

    if ($source === false) {
        throw new \RuntimeException(
            'Gambar tidak dapat diproses oleh GD.'
        );
    }
    $source = $this->correctImageOrientation(
    $source,
    $sourcePath,
    mime_content_type($sourcePath) ?: ''
    );

    $destination = null;

    try {
        $originalWidth = imagesx($source);
        $originalHeight = imagesy($source);

        if ($originalWidth < 1 || $originalHeight < 1) {
            throw new \RuntimeException(
                'Dimensi gambar tidak valid.'
            );
        }

        $scale = min(
            1,
            $maxEdge / max($originalWidth, $originalHeight)
        );

        $newWidth = max(
            1,
            (int) round($originalWidth * $scale)
        );

        $newHeight = max(
            1,
            (int) round($originalHeight * $scale)
        );

        $destination = imagecreatetruecolor(
            $newWidth,
            $newHeight
        );

        if ($destination === false) {
            throw new \RuntimeException(
                'Memori untuk gambar hasil tidak mencukupi.'
            );
        }

        // Pertahankan transparansi PNG dan WebP.
        imagealphablending($destination, false);
        imagesavealpha($destination, true);

        $transparent = imagecolorallocatealpha(
            $destination,
            0,
            0,
            0,
            127
        );

        imagefill($destination, 0, 0, $transparent);

        $resampled = imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        if (! $resampled) {
            throw new \RuntimeException(
                'Gagal mengubah ukuran gambar.'
            );
        }

        $disk = Storage::disk('family_photos');

        $disk->makeDirectory(dirname($targetPath));

        $absolutePath = $disk->path($targetPath);

        $success = imagewebp(
            $destination,
            $absolutePath,
            max(0, min(100, $quality))
        );

        if (! $success || ! $disk->exists($targetPath)) {
            throw new \RuntimeException(
                'Gagal membuat file gambar WebP.'
            );
        }
    } finally {
        if ($destination !== null && $destination !== false) {
            imagedestroy($destination);
        }

        imagedestroy($source);
    }
}

private function correctImageOrientation(
    \GdImage $image,
    string $sourcePath,
    string $mimeType
): \GdImage {
    if ($mimeType !== 'image/jpeg') {
        return $image;
    }

    if (! function_exists('exif_read_data')) {
        return $image;
    }

    try {
        $exif = @exif_read_data($sourcePath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, 270, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }
    } catch (\Throwable $e) {
        report($e);
    }

    return $image;
}


}
