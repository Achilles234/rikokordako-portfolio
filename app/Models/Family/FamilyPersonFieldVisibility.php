<?php

namespace App\Models\Family;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyPersonFieldVisibility extends Model
{
    protected $fillable = [
        'field_key',
        'visibility',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPerson::class,
            'family_person_id'
        );
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(
            FamilyGroup::class,
            'family_group_id'
        );
    }
}
