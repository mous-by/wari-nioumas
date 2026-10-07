<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Document administratif rattaché à un véhicule (carte grise, assurance,
 * vignette...) ou à un chauffeur (permis de conduite). Permet la surveillance
 * des échéances et l'historique des renouvellements (voir DocumentHistorique).
 */
class Document extends Model
{
    use HasFactory;

    public const TYPES_VEHICULE = [
        'carte_grise' => 'Carte grise',
        'assurance' => 'Assurance',
        'vignette' => 'Vignette',
        'autre' => 'Autre document',
    ];

    public const TYPES_CHAUFFEUR = [
        'permis_conduite' => 'Permis de conduite',
        'autre' => 'Autre document',
    ];

    /**
     * Seuils d'alerte en jours avant expiration, modifiables selon les
     * besoins de l'entreprise (voir la spécification de la fonctionnalité).
     */
    public const SEUIL_ATTENTION_JOURS = 30;

    public const SEUIL_PROCHE_JOURS = 7;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'type_document',
        'libelle_autre',
        'numero',
        'date_etablissement',
        'date_expiration',
        'fichier',
        'nom_original',
        'observations',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_etablissement' => 'date',
            'date_expiration' => 'date',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function historiques()
    {
        return $this->hasMany(DocumentHistorique::class)->latest();
    }

    /**
     * Types de documents proposés selon le genre de propriétaire.
     */
    public static function typesPour(string $documentableType): array
    {
        return $documentableType === Vehicule::class ? self::TYPES_VEHICULE : self::TYPES_CHAUFFEUR;
    }

    public function getTypeLibelleAttribute(): string
    {
        if ($this->type_document === 'autre' && $this->libelle_autre) {
            return $this->libelle_autre;
        }

        return self::typesPour($this->documentable_type)[$this->type_document] ?? $this->type_document;
    }

    /**
     * Jours restants avant expiration (négatif si déjà expiré). Null si le
     * document n'a pas de date d'expiration (ex. document "autre" permanent).
     */
    public function getJoursRestantsAttribute(): ?int
    {
        if (! $this->date_expiration) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($this->date_expiration, false);
    }

    /**
     * Statut de surveillance : expire / proche / attention / valide / null
     * (null = pas de date d'expiration, donc pas surveillé).
     */
    public function getStatutAttribute(): ?string
    {
        $jours = $this->jours_restants;

        if ($jours === null) {
            return null;
        }
        if ($jours < 0) {
            return 'expire';
        }
        if ($jours <= self::SEUIL_PROCHE_JOURS) {
            return 'proche';
        }
        if ($jours <= self::SEUIL_ATTENTION_JOURS) {
            return 'attention';
        }

        return 'valide';
    }

    public function getStatutLibelleAttribute(): ?string
    {
        return match ($this->statut) {
            'expire' => 'Expiré',
            'proche' => 'Échéance proche',
            'attention' => 'Attention',
            'valide' => 'Valide',
            default => null,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'expire' => 'bg-danger',
            'proche' => 'bg-warning text-dark',
            'attention' => 'bg-warning text-dark',
            'valide' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    /**
     * Libellé du propriétaire du document (immatriculation ou nom complet),
     * sans forcer le chargement de la relation si elle est déjà en mémoire.
     */
    public function getProprietaireLibelleAttribute(): string
    {
        $proprietaire = $this->documentable;

        if (! $proprietaire) {
            return '—';
        }

        return $proprietaire instanceof Vehicule ? $proprietaire->immatriculation : $proprietaire->nom_complet;
    }

    public function getUrlAttribute(): ?string
    {
        return $this->fichier ? asset('storage/'.$this->fichier) : null;
    }

    public function getCheminPathAttribute(): ?string
    {
        return $this->fichier ? storage_path('app/public/'.$this->fichier) : null;
    }

    public function scopeSurveilles($query)
    {
        return $query->whereNotNull('date_expiration');
    }

    public function scopeExpires($query)
    {
        return $query->whereNotNull('date_expiration')->whereDate('date_expiration', '<', Carbon::today());
    }

    /**
     * Expirant d'ici $jours jours inclus (sans compter les déjà expirés).
     */
    public function scopeExpirantDansLesJours($query, int $jours)
    {
        return $query->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '>=', Carbon::today())
            ->whereDate('date_expiration', '<=', Carbon::today()->copy()->addDays($jours));
    }

    /**
     * Crée ou met à jour le document "permis de conduite" d'un chauffeur à
     * partir de ses champs permis_numero / permis_date_validite, et journalise
     * un renouvellement si l'échéance a changé. Appelé par ChauffeurController
     * à chaque création/modification d'un chauffeur, pour que les champs
     * historiques du formulaire chauffeur alimentent automatiquement la
     * surveillance des échéances (sans rien changer à ce formulaire).
     */
    public static function enregistrerPermis(Chauffeur $chauffeur, ?int $userId): self
    {
        $document = static::where('documentable_type', Chauffeur::class)
            ->where('documentable_id', $chauffeur->id)
            ->where('type_document', 'permis_conduite')
            ->first();

        if (! $document) {
            return static::create([
                'documentable_type' => Chauffeur::class,
                'documentable_id' => $chauffeur->id,
                'type_document' => 'permis_conduite',
                'numero' => $chauffeur->permis_numero,
                'date_expiration' => $chauffeur->permis_date_validite,
                'created_by' => $userId,
            ]);
        }

        $ancienneEcheance = $document->date_expiration;

        $document->update([
            'numero' => $chauffeur->permis_numero,
            'date_expiration' => $chauffeur->permis_date_validite,
            'updated_by' => $userId,
        ]);

        if ($ancienneEcheance?->toDateString() !== $document->date_expiration?->toDateString()) {
            $document->historiques()->create([
                'ancienne_echeance' => $ancienneEcheance,
                'nouvelle_echeance' => $document->date_expiration,
                'user_id' => $userId,
            ]);
        }

        return $document;
    }
}
