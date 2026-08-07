<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculeEtatHistorique extends Model
{
    protected $fillable = [
        'vehicule_id',
        'ancien_etat',
        'nouveau_etat',
        'user_id',
    ];

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
