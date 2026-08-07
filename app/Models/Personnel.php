<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personnels';

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'telephone',
        'poste',
        'salaire_base',
        'banque',
        'numero_compte',
        'date_embauche',
        'statut',
        'user_id',
        'chauffeur_id',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'salaire_base' => 'decimal:2',
            'date_embauche' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Personnel $personnel) {
            if (empty($personnel->matricule)) {
                $personnel->matricule = static::genererMatricule();
            }
        });
    }

    public static function genererMatricule(): string
    {
        $dernier = static::withTrashed()
            ->where('matricule', 'like', 'EMP-%')
            ->orderByRaw('CAST(SUBSTRING(matricule, 5) AS UNSIGNED) DESC')
            ->value('matricule');

        $numero = $dernier ? ((int) substr($dernier, 4)) + 1 : 1;

        return 'EMP-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    public function salaireHistoriques()
    {
        return $this->hasMany(PersonnelSalaireHistorique::class)->latest();
    }

    public function bulletins()
    {
        return $this->hasMany(Bulletin::class)->latest('periode_annee')->latest('periode_mois');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
