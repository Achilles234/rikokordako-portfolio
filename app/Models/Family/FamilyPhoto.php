<?php

namespace App\Models\Family;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyPhoto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_photo_album_id',
        'file_path',
        'thumbnail_path',
        'preview_path',
        'original_name',
        'mime_type',
        'file_size',
        'width',
        'height',
        'caption',
        'taken_at',
        'uploaded_by',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'taken_at' => 'datetime',
            'deleted_with_album_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPhotoAlbum::class,
            'family_photo_album_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(
            FamilyPhotoFavorite::class,
            'family_photo_id'
        );
    }
}
