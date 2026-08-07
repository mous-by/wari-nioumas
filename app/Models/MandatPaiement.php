<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MandatPaiement extends Model
{
    use HasFactory;

    protected $table = 'mandats_paiement';

    public const MOIS = Bulletin::MOIS;

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'signe' => 'Signé',
        'depose' => 'Déposé en banque',
        'paye' => 'Payé',
    ];

    protected $fillable = [
        'numero',
        'date_mandat',
        'banque',
        'periode_mois',
        'periode_annee',
        'montant_total',
        'statut',
        'signataire_id',
        'date_signature',
        'observations',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_mandat' => 'date',
            'date_signature' => 'datetime',
            'montant_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MandatPaiement $mandat) {
            if (empty($mandat->numero)) {
                $mandat->numero = static::genererNumero();
            }
        });
    }

    public static function genererNumero(): string
    {
        $annee = date('Y');
        $dernier = static::where('numero', 'like', "MP-{$annee}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero, "-", -1) AS UNSIGNED) DESC')
            ->value('numero');

        $numero = $dernier ? ((int) substr($dernier, strrpos($dernier, '-') + 1)) + 1 : 1;

        return "MP-{$annee}-".str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    public function lignes()
    {
        return $this->hasMany(MandatLigne::class);
    }

    public function signataire()
    {
        return $this->belongsTo(User::class, 'signataire_id');
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

    public function estSigne(): bool
    {
        return in_array($this->statut, ['signe', 'depose', 'paye'], true);
    }
}
