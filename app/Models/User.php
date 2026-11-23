<?php

namespace App\Models;

use App\Models\Family\FamilyMembershipStatusLog;
use App\Models\Family\FamilyPerson;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function familyPerson(): HasOne
    {
        return $this->hasOne(FamilyPerson::class);
    }

    public function familyMembershipStatusChanges(): HasMany
    {
        return $this->hasMany(
            FamilyMembershipStatusLog::class,
            'changed_by'
        );
    }
}
