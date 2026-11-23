<?php

namespace App\Models;

use App\Models\Family\FamilyMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyAgendaParticipant extends Model
{
    protected $fillable = [
        'family_agenda_id',
        'family_member_id',
        'attendance_status',
        'response_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'response_at' => 'datetime',
        ];
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(
            FamilyAgenda::class,
            'family_agenda_id'
        );
    }

    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(
            FamilyMember::class,
            'family_member_id'
        );
    }
}
