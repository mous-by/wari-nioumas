<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicule_id',
        'chauffeur_id',
        'montant_journalier',
        'date_debut',
        'date_fin',
        'motif_fin',
        'observations',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'montant_journalier' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estActive(): bool
    {
        return is_null($this->date_fin);
    }
}
