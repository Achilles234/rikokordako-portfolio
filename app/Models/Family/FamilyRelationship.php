<?php

namespace App\Models\Family;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyRelationship extends Model
{
    protected $fillable = [
        'person_id',
        'related_person_id',
        'relationship_type',
        'relationship_subtype',
        'parent_role',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPerson::class,
            'person_id'
        );
    }

    public function relatedPerson(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPerson::class,
            'related_person_id'
        );
    }
}
