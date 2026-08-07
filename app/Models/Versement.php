<?php

namespace App\Models;

use App\Support\CaisseAuto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Versement extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Alimentation automatique de la caisse ouverte (entrée).
        $sync = fn (Versement $v) => CaisseAuto::synchroniser(
            $v, 'entree', 'Versement — '.(optional($v->chauffeur)->nom_complet ?? 'chauffeur'),
            (float) $v->montant, $v->date_versement, $v->user_id
        );

        static::created($sync);
        static::updated($sync);
        static::deleted(fn (Versement $v) => CaisseAuto::supprimer($v));
    }

    protected $fillable = [
        'chauffeur_id',
        'date_versement',
        'montant',
        'observations',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_versement' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
