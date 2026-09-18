<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttestationVente extends Model
{
    use HasFactory;

    public const TYPES_BIEN = [
        'vehicule' => 'Véhicule',
        'piece' => 'Pièce détachée',
        'equipement' => 'Équipement',
        'autre' => 'Autre',
    ];

    public const MODES_PAIEMENT = [
        'especes' => 'Espèces',
        'cheque' => 'Chèque bancaire ou postal',
        'virement' => 'Virement',
        'autre' => 'Autre',
    ];

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'validee' => 'Validée',
    ];

    protected $table = 'attestations_ventes';

    // Correspond aux valeurs par défaut de la migration : sans ça, un modèle
    // fraîchement créé (avant rechargement depuis la base) aurait ces
    // attributs à null en mémoire, faisant planter statut_libelle etc.
    protected $attributes = [
        'statut' => 'brouillon',
        'montant_paye' => 0,
        'avec_temoins' => false,
    ];

    protected $fillable = [
        'numero',
        'type_bien',
        'vehicule_id',
        'marque',
        'modele',
        'immatriculation',
        'numero_chassis',
        'annee',
        'couleur',
        'autres_caracteristiques',
        'designation',
        'reference',
        'quantite',
        'etat',
        'description',
        'vendeur_representant',
        'vendeur_nina',
        'acheteur_nom',
        'acheteur_nina',
        'acheteur_adresse',
        'avec_temoins',
        'temoin_1_nom',
        'temoin_2_nom',
        'montant_total',
        'montant_paye',
        'mode_paiement',
        'date_vente',
        'lieu_vente',
        'observations',
        'statut',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_vente' => 'date',
            'avec_temoins' => 'boolean',
            'montant_total' => 'decimal:2',
            'montant_paye' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AttestationVente $attestation) {
            if (empty($attestation->numero)) {
                $attestation->numero = static::genererNumero();
            }
        });
    }

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $dernier = static::where('numero', 'like', "AV-{$annee}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero, "-", -1) AS UNSIGNED) DESC')
            ->value('numero');

        $numero = $dernier ? ((int) substr($dernier, strrpos($dernier, '-') + 1)) + 1 : 1;

        return "AV-{$annee}-".str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function reste(): float
    {
        return (float) $this->montant_total - (float) $this->montant_paye;
    }

    public function estValidee(): bool
    {
        return $this->statut === 'validee';
    }

    public function getTypeBienLibelleAttribute(): string
    {
        return self::TYPES_BIEN[$this->type_bien] ?? $this->type_bien;
    }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getModePaiementLibelleAttribute(): ?string
    {
        return $this->mode_paiement ? (self::MODES_PAIEMENT[$this->mode_paiement] ?? $this->mode_paiement) : null;
    }

    /**
     * Libellé court du bien vendu, pour les listes (ex. "AA123BC — Mercedes 210"
     * ou "Alternateur x2").
     */
    public function getBienLibelleAttribute(): string
    {
        if ($this->type_bien === 'vehicule') {
            return trim(($this->immatriculation ? $this->immatriculation.' — ' : '').trim($this->marque.' '.$this->modele)) ?: 'Véhicule';
        }

        return $this->designation ?: self::TYPES_BIEN[$this->type_bien];
    }
}
