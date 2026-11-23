<?php

namespace App\Models\Family;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyMember extends Model
{
    protected $fillable = [
        'family_group_id',
        'family_person_id',
        'role_id',
        'status',
        'joined_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class, 'family_group_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(FamilyPerson::class, 'family_person_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function statusLogs(): HasMany
    {
        return $this->hasMany(
            FamilyMembershipStatusLog::class,
            'family_member_id'
        )->orderBy('created_at')->orderBy('id');
    }
    public function photoFavorites(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(
        FamilyPhotoFavorite::class,
        'family_member_id'
    );
}
}
