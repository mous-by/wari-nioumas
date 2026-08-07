<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'immatriculation',
        'marque',
        'modele',
        'type',
        'annee',
        'etat',
        'observations',
    ];

    public function etatHistoriques()
    {
        return $this->hasMany(VehiculeEtatHistorique::class)->latest();
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class)->latest('date_debut');
    }

    public function affectationActive()
    {
        return $this->hasOne(Affectation::class)->whereNull('date_fin');
    }

    public function chauffeurActuel()
    {
        return $this->affectationActive?->chauffeur;
    }
}
