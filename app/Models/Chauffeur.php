<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Chauffeur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'nina',
        'permis_numero',
        'permis_date_validite',
        'date_embauche',
        'statut',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'permis_date_validite' => 'date',
            'date_embauche' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Chauffeur $chauffeur) {
            if (empty($chauffeur->matricule)) {
                $chauffeur->matricule = static::genererMatricule();
            }
        });
    }

    public static function genererMatricule(): string
    {
        $dernier = static::withTrashed()
            ->where('matricule', 'like', 'CH-%')
            ->orderByRaw('CAST(SUBSTRING(matricule, 4) AS UNSIGNED) DESC')
            ->value('matricule');

        $numero = $dernier ? ((int) substr($dernier, 3)) + 1 : 1;

        return 'CH-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    public function statutHistoriques()
    {
        return $this->hasMany(ChauffeurStatutHistorique::class)->latest();
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class)->latest('date_debut');
    }

    public function affectationActive()
    {
        return $this->hasOne(Affectation::class)->whereNull('date_fin');
    }

    public function vehiculeActuel()
    {
        return $this->affectationActive?->vehicule;
    }

    public function versements()
    {
        return $this->hasMany(Versement::class)->latest('date_versement');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * Montant journalier attendu du chauffeur, issu de son affectation active.
     */
    public function montantJournalierActuel(): float
    {
        return (float) ($this->affectationActive?->montant_journalier ?? 0);
    }

    /**
     * Montant total DÛ (« compte à rebours ») accumulé automatiquement jusqu'à
     * la date donnée (aujourd'hui par défaut). Pour chaque affectation, on
     * compte les jours écoulés (montant journalier × jours), puis on retranche
     * les jours d'absence acceptée qui tombent dans cette période.
     */
    public function montantDu(?Carbon $jusquA = null): float
    {
        $jusquA = ($jusquA ? $jusquA->copy() : Carbon::today())->startOfDay();

        $absencesAcceptees = $this->absences
            ->where('statut', 'acceptee');

        $total = 0.0;

        foreach ($this->affectations as $affectation) {
            $debut = $affectation->date_debut->copy()->startOfDay();

            if ($debut->gt($jusquA)) {
                continue; // affectation qui commence dans le futur
            }

            $fin = $affectation->date_fin
                ? $affectation->date_fin->copy()->startOfDay()
                : $jusquA->copy();

            if ($fin->gt($jusquA)) {
                $fin = $jusquA->copy();
            }

            $jours = $debut->diffInDays($fin) + 1; // bornes incluses

            $joursAbsence = 0;
            foreach ($absencesAcceptees as $absence) {
                $joursAbsence += static::joursChevauchement(
                    $absence->date_debut, $absence->date_fin, $debut, $fin
                );
            }

            $total += max(0, $jours - $joursAbsence) * (float) $affectation->montant_journalier;
        }

        return $total;
    }

    /**
     * Total déjà versé par le chauffeur.
     */
    public function totalVerse(): float
    {
        return (float) ($this->relationLoaded('versements')
            ? $this->versements->sum('montant')
            : $this->versements()->sum('montant'));
    }

    /**
     * Solde restant dû = montant dû − total versé (positif = reste à payer).
     */
    public function solde(?Carbon $jusquA = null): float
    {
        return $this->montantDu($jusquA) - $this->totalVerse();
    }

    /**
     * Nombre de jours de chevauchement entre deux intervalles [début, fin]
     * (bornes incluses). Retourne 0 s'ils ne se recouvrent pas.
     */
    protected static function joursChevauchement(Carbon $aDebut, Carbon $aFin, Carbon $bDebut, Carbon $bFin): int
    {
        $debut = $aDebut->gt($bDebut) ? $aDebut : $bDebut;
        $fin = $aFin->lt($bFin) ? $aFin : $bFin;

        if ($debut->gt($fin)) {
            return 0;
        }

        return $debut->startOfDay()->diffInDays($fin->startOfDay()) + 1;
    }

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
