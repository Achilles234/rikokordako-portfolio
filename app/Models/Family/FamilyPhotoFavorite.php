<?php

namespace App\Models\Family;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyPhotoFavorite extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'family_photo_id',
        'user_id',
        'family_member_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPhoto::class,
            'family_photo_id'
        );
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(
            FamilyMember::class,
            'family_member_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
