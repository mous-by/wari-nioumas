<?php

namespace App\Models;

use App\Support\CaisseAuto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CasSocial extends Model
{
    use HasFactory;

    protected $table = 'cas_sociaux';

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'en_attente' => 'En attente',
        'approuve' => 'Approuvé',
        'paye' => 'Payé',
        'rejete' => 'Rejeté',
        'annule' => 'Annulé',
    ];

    public const MODES_PAIEMENT = [
        'especes' => 'Espèces',
        'virement' => 'Virement',
        'mobile_money' => 'Mobile Money',
        'cheque' => 'Chèque',
        'autre' => 'Autre',
    ];

    protected $attributes = [
        'statut' => 'brouillon',
    ];

    protected $fillable = [
        'numero',
        'personnel_id',
        'type_cas_social_id',
        'date_cas',
        'date_demande',
        'motif',
        'observations',
        'montant_demande',
        'montant_accorde',
        'mode_paiement',
        'statut',
        'motif_rejet',
        'motif_annulation',
        'demandeur_id',
        'valide_par_id',
        'date_validation',
        'paye_par_id',
        'date_paiement',
        'annule_par_id',
        'date_annulation',
    ];

    protected function casts(): array
    {
        return [
            'date_cas' => 'date',
            'date_demande' => 'date',
            'montant_demande' => 'decimal:2',
            'montant_accorde' => 'decimal:2',
            'date_validation' => 'datetime',
            'date_paiement' => 'datetime',
            'date_annulation' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CasSocial $cas) {
            if (empty($cas->numero)) {
                $cas->numero = static::genererNumero();
            }
            if (empty($cas->demandeur_id)) {
                $cas->demandeur_id = auth()->id();
            }
        });
    }

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $dernier = static::where('numero', 'like', "CS-{$annee}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero, "-", -1) AS UNSIGNED) DESC')
            ->value('numero');

        $numero = $dernier ? ((int) substr($dernier, strrpos($dernier, '-') + 1)) + 1 : 1;

        return "CS-{$annee}-".str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function type()
    {
        return $this->belongsTo(TypeCasSocial::class, 'type_cas_social_id');
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }

    public function payeur()
    {
        return $this->belongsTo(User::class, 'paye_par_id');
    }

    public function annulePar()
    {
        return $this->belongsTo(User::class, 'annule_par_id');
    }

    public function documents()
    {
        return $this->hasMany(CasSocialDocument::class);
    }

    /**
     * Un cas payé puis annulé a DEUX lignes (l'originale + sa
     * contre-passation) : morphMany, pas morphOne.
     */
    public function mouvements()
    {
        return $this->morphMany(MouvementCaisse::class, 'source');
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getModePaiementLibelleAttribute(): ?string
    {
        return $this->mode_paiement ? (self::MODES_PAIEMENT[$this->mode_paiement] ?? $this->mode_paiement) : null;
    }

    public function estModifiable(): bool
    {
        return in_array($this->statut, ['brouillon', 'en_attente']);
    }

    public function soumettre(): void
    {
        if ($this->statut !== 'brouillon') {
            throw new \RuntimeException("Seul un cas en brouillon peut être soumis.");
        }

        $this->update(['statut' => 'en_attente']);
    }

    public function approuver(User $dg, float $montantAccorde): void
    {
        if ($this->statut !== 'en_attente') {
            throw new \RuntimeException("Seul un cas en attente peut être approuvé.");
        }

        $this->update([
            'statut' => 'approuve',
            'montant_accorde' => $montantAccorde,
            'valide_par_id' => $dg->id,
            'date_validation' => now(),
        ]);
    }

    public function rejeter(User $dg, string $motif): void
    {
        if ($this->statut !== 'en_attente') {
            throw new \RuntimeException("Seul un cas en attente peut être rejeté.");
        }

        $this->update([
            'statut' => 'rejete',
            'motif_rejet' => $motif,
            'valide_par_id' => $dg->id,
            'date_validation' => now(),
        ]);
    }

    public function reprendre(): void
    {
        if ($this->statut !== 'rejete') {
            throw new \RuntimeException("Seul un cas rejeté peut être repris.");
        }

        $this->update(['statut' => 'brouillon', 'motif_rejet' => null]);
    }

    /**
     * Paiement : crée la sortie de caisse EXACTEMENT une fois pour ce cas.
     * Verrouillage de ligne + vérification dans la même transaction pour
     * empêcher tout double paiement en cas de double clic/requête concurrente.
     */
    public function payer(User $payeur): void
    {
        DB::transaction(function () use ($payeur) {
            $cas = static::whereKey($this->id)->lockForUpdate()->first();

            if ($cas->statut !== 'approuve') {
                throw new \RuntimeException("Seul un cas approuvé peut être payé.");
            }

            $caisse = Caisse::ouverte()->latest('date_ouverture')->first();
            if (! $caisse) {
                throw new \RuntimeException('Aucune caisse ouverte : impossible d\'enregistrer le paiement.');
            }

            if ($cas->mouvements()->originales()->where('type', 'sortie')->exists()) {
                throw new \RuntimeException('Ce cas a déjà été payé.');
            }

            $caisse->mouvements()->create([
                'type' => 'sortie',
                'libelle' => "Aide sociale - {$cas->type->libelle} - {$cas->personnel->nom_complet}",
                'montant' => $cas->montant_accorde,
                'date_mouvement' => now(),
                'user_id' => $payeur->id,
                'source_type' => static::class,
                'source_id' => $cas->id,
            ]);

            $cas->update([
                'statut' => 'paye',
                'paye_par_id' => $payeur->id,
                'date_paiement' => now(),
            ]);

            $this->setRawAttributes($cas->fresh()->getAttributes());
        });
    }

    /**
     * Annulation : si le cas était payé, contre-passe le mouvement (jamais
     * de suppression) avant de marquer le cas comme annulé. Idempotent via
     * CaisseAuto::contrepasser() + le verrou de ligne.
     */
    public function annuler(User $agent, string $motif): void
    {
        DB::transaction(function () use ($agent, $motif) {
            $cas = static::whereKey($this->id)->lockForUpdate()->first();

            if (! in_array($cas->statut, ['brouillon', 'en_attente', 'approuve', 'paye'])) {
                throw new \RuntimeException('Ce cas ne peut plus être annulé.');
            }

            if ($cas->statut === 'paye') {
                $original = $cas->mouvements()->originales()->where('type', 'sortie')->first();
                if ($original) {
                    CaisseAuto::contrepasser($original, "Annulation aide sociale - {$cas->numero}", $agent->id);
                }
            }

            $cas->update([
                'statut' => 'annule',
                'annule_par_id' => $agent->id,
                'date_annulation' => now(),
                'motif_annulation' => $motif,
            ]);

            $this->setRawAttributes($cas->fresh()->getAttributes());
        });
    }
}
