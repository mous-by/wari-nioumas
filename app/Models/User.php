<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'photo',
        'signature',
        'cachet',
        'password',
        'actif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature ? asset('storage/'.$this->signature) : null;
    }

    public function getCachetUrlAttribute(): ?string
    {
        return $this->cachet ? asset('storage/'.$this->cachet) : null;
    }
}
