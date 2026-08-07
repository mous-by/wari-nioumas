<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accident extends Model
{
    use HasFactory;

    public const GRAVITES = [
        'leger' => 'Léger',
        'moyen' => 'Moyen',
        'grave' => 'Grave',
    ];

    public const RESPONSABILITES = [
        'chauffeur' => 'Chauffeur',
        'tiers' => 'Tiers',
        'partagee' => 'Partagée',
        'indeterminee' => 'Indéterminée',
    ];

    public const STATUTS = [
        'en_cours' => 'En cours',
        'clos' => 'Clos',
    ];

    protected $fillable = [
        'vehicule_id',
        'chauffeur_id',
        'date_accident',
        'lieu',
        'gravite',
        'responsabilite',
        'description',
        'cout_reparation',
        'decision',
        'statut',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_accident' => 'date',
            'cout_reparation' => 'decimal:2',
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

    public function getGraviteLibelleAttribute(): string
    {
        return self::GRAVITES[$this->gravite] ?? $this->gravite;
    }

    public function getResponsabiliteLibelleAttribute(): string
    {
        return self::RESPONSABILITES[$this->responsabilite] ?? $this->responsabilite;
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }
}
