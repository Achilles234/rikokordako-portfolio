<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyAgendaReminder extends Model
{
    protected $fillable = [
        'family_agenda_id',
        'minutes_before',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minutes_before' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(
            FamilyAgenda::class,
            'family_agenda_id'
        );
    }
        public function agendaParticipations(): HasMany
    {
        return $this->hasMany(
            \App\Models\FamilyAgendaParticipant::class,
            'family_member_id'
        );
    }
}
