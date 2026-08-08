<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    use HasFactory;

    /**
     * Périodicités possibles du montant, choisies librement par affectation.
     * « journalier » = comportement d'origine (montant × jours). Les autres sont
     * des forfaits par période (ex. camionettes, qui ne se paient pas au jour) :
     * le montant est dû en entier au début de chaque période.
     */
    public const PERIODICITES = ['journalier', 'hebdomadaire', 'mensuel', 'trimestriel', 'semestriel'];

    protected $fillable = [
        'vehicule_id',
        'chauffeur_id',
        'montant_journalier',
        'periodicite',
        'date_debut',
        'date_fin',
        'motif_fin',
        'observations',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'montant_journalier' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
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

    public function estActive(): bool
    {
        return is_null($this->date_fin);
    }

    /**
     * Nombre de mois d'une période selon la périodicité (0 si la période n'est
     * pas comptée en mois — journalier ou hebdomadaire).
     */
    public function moisParPeriode(): int
    {
        return [
            'mensuel' => 1,
            'trimestriel' => 3,
            'semestriel' => 6,
        ][$this->periodicite] ?? 0;
    }

    /**
     * Nombre de jours d'une période comptée en jours (7 = hebdomadaire ;
     * 0 pour les périodes mensuelles ou journalières).
     */
    public function joursParPeriode(): int
    {
        return [
            'hebdomadaire' => 7,
        ][$this->periodicite] ?? 0;
    }

    /**
     * Suffixe lisible du montant, ex. « / jour », « / mois ».
     */
    public function periodiciteSuffixe(): string
    {
        return [
            'journalier' => '/ jour',
            'hebdomadaire' => '/ semaine',
            'mensuel' => '/ mois',
            'trimestriel' => '/ trimestre',
            'semestriel' => '/ semestre',
        ][$this->periodicite] ?? '/ jour';
    }

    public function periodiciteLabel(): string
    {
        return ucfirst($this->periodicite ?? 'journalier');
    }
}
