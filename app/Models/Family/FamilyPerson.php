<?php

namespace App\Models\Family;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FamilyPerson extends Model
{
    protected $table = 'family_persons';

    protected $fillable = [
        'user_id',
        'full_name',
        'nickname',
        'sex',
        'birth_date',
        'birth_place',
        'death_date',
        'death_place',
        'life_status',
        'photo_path',
        'biography',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(
            FamilyRelationship::class,
            'person_id'
        );
    }

    public function inverseRelationships(): HasMany
    {
        return $this->hasMany(
            FamilyRelationship::class,
            'related_person_id'
        );
    }

       public function details(): HasOne
{
    return $this->hasOne(
        FamilyPersonDetail::class,
        'family_person_id'
    );
}

public function privacySettings(): HasOne
{
    return $this->hasOne(
        FamilyPersonPrivacySetting::class,
        'family_person_id'
    );
}

public function fieldVisibilities(): HasMany
{
    return $this->hasMany(
        FamilyPersonFieldVisibility::class,
        'family_person_id'
    );
}
}
