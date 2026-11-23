<?php

namespace App\Models\Family;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyPersonPrivacySetting extends Model
{
    protected $fillable = [
        'default_field_visibility',
        'show_profile_updates_in_timeline',
    ];

    protected function casts(): array
    {
        return [
            'show_profile_updates_in_timeline' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPerson::class,
            'family_person_id'
        );
    }
}
