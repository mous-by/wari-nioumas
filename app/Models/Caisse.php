<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'solde_ouverture',
        'date_ouverture',
        'solde_fermeture',
        'date_fermeture',
        'statut',
        'observations',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'solde_ouverture' => 'decimal:2',
            'solde_fermeture' => 'decimal:2',
            'date_ouverture' => 'datetime',
            'date_fermeture' => 'datetime',
        ];
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementCaisse::class)->latest('date_mouvement')->latest('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOuverte($query)
    {
        return $query->where('statut', 'ouverte');
    }

    public function estOuverte(): bool
    {
        return $this->statut === 'ouverte';
    }

    public function totalEntrees(): float
    {
        return (float) ($this->relationLoaded('mouvements')
            ? $this->mouvements->where('type', 'entree')->sum('montant')
            : $this->mouvements()->where('type', 'entree')->sum('montant'));
    }

    public function totalSorties(): float
    {
        return (float) ($this->relationLoaded('mouvements')
            ? $this->mouvements->where('type', 'sortie')->sum('montant')
            : $this->mouvements()->where('type', 'sortie')->sum('montant'));
    }

    /**
     * Solde courant = solde d'ouverture + entrées − sorties.
     */
    public function soldeCourant(): float
    {
        return (float) $this->solde_ouverture + $this->totalEntrees() - $this->totalSorties();
    }
}
