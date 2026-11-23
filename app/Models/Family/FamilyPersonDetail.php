<?php

namespace App\Models\Family;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyPersonDetail extends Model
{
    protected $fillable = [
        'home_address',
        'phone',
        'email',
        'occupation',
        'education',
        'hobbies',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPerson::class,
            'family_person_id'
        );
    }
}
