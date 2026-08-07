<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    public const STATUTS = [
        'en_attente' => 'En attente',
        'approuvee' => 'Approuvée',
        'refusee' => 'Refusée',
    ];

    protected $fillable = [
        'type',
        'libelle',
        'demandeur_id',
        'payload',
        'statut',
        'valideur_id',
        'motif',
        'decidee_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'decidee_at' => 'datetime',
        ];
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valideur_id');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }
}
