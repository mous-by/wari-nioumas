<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementCaisse extends Model
{
    use HasFactory;

    public const TYPES = [
        'entree' => 'Entrée',
        'sortie' => 'Sortie',
    ];

    protected $fillable = [
        'caisse_id',
        'type',
        'libelle',
        'montant',
        'date_mouvement',
        'user_id',
        'source_type',
        'source_id',
        'reversal_of_id',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_mouvement' => 'date',
        ];
    }

    public function caisse()
    {
        return $this->belongsTo(Caisse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function reversalOf()
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reversal_of_id');
    }

    public function scopeOriginales($query)
    {
        return $query->whereNull('reversal_of_id');
    }

    /**
     * Vrai si le mouvement provient d'une source automatique (versement/dépense).
     */
    public function estAutomatique(): bool
    {
        return ! is_null($this->source_id);
    }

    public function estContrepassation(): bool
    {
        return ! is_null($this->reversal_of_id);
    }

    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
