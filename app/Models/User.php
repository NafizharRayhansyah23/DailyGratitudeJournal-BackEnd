<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'birth_date',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    /**
     * Mutator to ensure passwords are hashed when set on the model.
     * If a hashed value is provided, it will not be re-hashed.
     */
    public function setPasswordAttribute($value)
    {
        if (! $value) {
            $this->attributes['password'] = $value;
            return;
        }

        // Detect common hashed prefixes (bcrypt/argon2)
        if (Str::startsWith($value, '$2y$') || Str::startsWith($value, '$argon2')) {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }
}
