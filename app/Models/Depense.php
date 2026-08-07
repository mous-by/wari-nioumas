<?php

namespace App\Models;

use App\Support\CaisseAuto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Alimentation automatique de la caisse ouverte (sortie).
        $sync = fn (Depense $d) => CaisseAuto::synchroniser(
            $d, 'sortie', 'Dépense — '.$d->categorie_libelle,
            (float) $d->montant, $d->date_depense, $d->user_id
        );

        static::created($sync);
        static::updated($sync);
        static::deleted(fn (Depense $d) => CaisseAuto::supprimer($d));
    }

    public const CATEGORIES = [
        'carburant' => 'Carburant',
        'entretien' => 'Entretien',
        'reparation' => 'Réparation',
        'pneus' => 'Pneus',
        'assurance' => 'Assurance',
        'visite_technique' => 'Visite technique',
        'autres' => 'Autres',
    ];

    protected $fillable = [
        'vehicule_id',
        'categorie',
        'montant',
        'date_depense',
        'description',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_depense' => 'date',
        ];
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCategorieLibelleAttribute(): string
    {
        return self::CATEGORIES[$this->categorie] ?? $this->categorie;
    }
}
