<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    public const TYPES = [
        'panne' => 'Panne',
        'contravention' => 'Contravention',
        'vol' => 'Vol',
        'agression' => 'Agression',
        'retard' => 'Retard',
        'autre' => 'Autre',
    ];

    public const GRAVITES = [
        'leger' => 'Léger',
        'moyen' => 'Moyen',
        'grave' => 'Grave',
    ];

    public const STATUTS = [
        'ouvert' => 'Ouvert',
        'resolu' => 'Résolu',
    ];

    protected $fillable = [
        'vehicule_id',
        'chauffeur_id',
        'date_incident',
        'type',
        'gravite',
        'description',
        'cout',
        'decision',
        'statut',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_incident' => 'date',
            'cout' => 'decimal:2',
        ];
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getGraviteLibelleAttribute(): string
    {
        return self::GRAVITES[$this->gravite] ?? $this->gravite;
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }
}
