<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'chauffeur_id',
        'date_debut',
        'date_fin',
        'motif',
        'statut',
        'user_id',
        'validee_par',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validee_par');
    }

    public function nombreJours(): int
    {
        return $this->date_debut->diffInDays($this->date_fin) + 1;
    }
}
