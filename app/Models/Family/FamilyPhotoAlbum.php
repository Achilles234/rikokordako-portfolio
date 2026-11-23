<?php

namespace App\Models\Family;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyPhotoAlbum extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'name',
        'slug',
        'description',
        'cover_photo_id',
        'event_date',
        'created_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(
            FamilyGroup::class,
            'family_group_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function photos(): HasMany
    {
        return $this->hasMany(
            FamilyPhoto::class,
            'family_photo_album_id'
        );
    }

    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPhoto::class,
            'cover_photo_id'
        );
    }
}
