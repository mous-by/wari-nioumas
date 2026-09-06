<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCasSocial extends Model
{
    use HasFactory;

    protected $table = 'type_cas_sociaux';

    protected $fillable = [
        'libelle',
        'description',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function casSociaux()
    {
        return $this->hasMany(CasSocial::class);
    }
}
