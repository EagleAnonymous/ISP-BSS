<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subscriber(): HasOne
    {
        return $this->hasOne(Subscriber::class);
    }

    public function technicalStaff(): HasOne
    {
        return $this->hasOne(TechnicalStaff::class);
    }

    /**
     * The service account used as the `created_by` actor for automated,
     * unattended actions (e.g. system-applied late fees), since those
     * columns are non-nullable and expect a real user row.
     */
    public static function systemUser(): self
    {
        return static::firstOrCreate(
            ['email' => 'system@smart-isp.internal'],
            ['name' => 'System', 'password' => Hash::make(Str::random(40))]
        );
    }
}
