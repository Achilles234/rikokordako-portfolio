<?php

namespace App\Models;

use App\Models\Family\FamilyGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyAgenda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'title',
        'category',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'is_all_day',
        'audience_type',
        'status',
        'cancellation_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_all_day' => 'boolean',
        ];
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class, 'family_group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(
            FamilyAgendaParticipant::class,
            'family_agenda_id'
        );
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(
            FamilyAgendaReminder::class,
            'family_agenda_id'
        );
    }
}
