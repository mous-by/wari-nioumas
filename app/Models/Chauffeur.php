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
        'date_naissance',
        'lieu_naissance',
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
            'date_naissance' => 'date',
            'permis_date_validite' => 'date',
            'date_embauche' => 'date',
        ];
    }

    /**
     * Naissance formatée « 10/07/1992 à Bamako ». La préposition « à » n'apparaît
     * que si la date de naissance est connue ; sinon on affiche ce qui est
     * disponible (date seule ou lieu seul), ou null si rien n'est renseigné.
     */
    protected function naissance(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function () {
                $date = $this->date_naissance?->format('d/m/Y');
                $lieu = $this->lieu_naissance ? trim($this->lieu_naissance) : null;

                if ($date && $lieu) {
                    return $date.' à '.$lieu;
                }

                return $date ?: $lieu;
            },
        );
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
     * Suffixe de périodicité de l'affectation active (« / jour », « / mois »…).
     */
    public function periodiciteActuelleSuffixe(): string
    {
        return $this->affectationActive?->periodiciteSuffixe() ?? '/ jour';
    }

    /**
     * Montant total DÛ (« compte à rebours ») accumulé automatiquement jusqu'à
     * la date donnée (aujourd'hui par défaut).
     *
     * - Affectation JOURNALIÈRE : on compte les jours écoulés (montant × jours)
     *   puis on retranche les jours d'absence acceptée.
     * - Affectation PÉRIODIQUE mensuelle / trimestrielle / semestrielle
     *   (ex. camionettes) : forfait dû en entier au début de chaque période ; un
     *   nouveau forfait s'ajoute à chaque période commencée. Les absences ne
     *   réduisent pas le forfait.
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

            $moisParPeriode = $affectation->moisParPeriode();

            if ($moisParPeriode > 0) {
                // Forfait par mois/trimestre/semestre : nombre de périodes commencées.
                $moisEcoules = ($fin->year - $debut->year) * 12 + ($fin->month - $debut->month);
                if ($fin->day < $debut->day) {
                    $moisEcoules--;
                }
                $moisEcoules = max(0, $moisEcoules);

                $periodes = intdiv($moisEcoules, $moisParPeriode) + 1;
                $total += $periodes * (float) $affectation->montant_journalier;

                continue;
            }

            $joursParPeriode = $affectation->joursParPeriode();

            if ($joursParPeriode > 0) {
                // Forfait hebdomadaire : nombre de semaines commencées (période de 7 jours).
                $joursEcoules = (int) $debut->diffInDays($fin);
                $periodes = intdiv($joursEcoules, $joursParPeriode) + 1;
                $total += $periodes * (float) $affectation->montant_journalier;

                continue;
            }

            // Comportement journalier d'origine — inchangé.
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
