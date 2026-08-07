<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChauffeurStatutHistorique extends Model
{
    protected $fillable = [
        'chauffeur_id',
        'ancien_statut',
        'nouveau_statut',
        'user_id',
    ];

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
