<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    use HasFactory;

    public const MOIS = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'valide' => 'Validé',
        'paye' => 'Payé',
    ];

    protected $fillable = [
        'personnel_id',
        'periode_mois',
        'periode_annee',
        'salaire_base',
        'primes',
        'retenues',
        'net_a_payer',
        'observations',
        'statut',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'salaire_base' => 'decimal:2',
            'primes' => 'decimal:2',
            'retenues' => 'decimal:2',
            'net_a_payer' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Le net à payer est toujours recalculé à l'enregistrement.
        static::saving(function (Bulletin $bulletin) {
            $bulletin->net_a_payer = (float) $bulletin->salaire_base
                + (float) $bulletin->primes
                - (float) $bulletin->retenues;
        });
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPeriodeLibelleAttribute(): string
    {
        return (self::MOIS[$this->periode_mois] ?? $this->periode_mois).' '.$this->periode_annee;
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }
}
