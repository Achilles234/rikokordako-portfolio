<?php

namespace App\Models\Family;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'group_type',
        'description',
        'status',
        'archived_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function personFieldVisibilities(): HasMany
{
    return $this->hasMany(
        FamilyPersonFieldVisibility::class,
        'family_group_id'
    );
}


public function activityLogs(): HasMany
{
    return $this->hasMany(FamilyGroupActivityLog::class, 'family_group_id')
        ->orderBy('created_at')
        ->orderBy('id');
}
    public function photoAlbums(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(
        FamilyPhotoAlbum::class,
        'family_group_id'
    );
}
}
