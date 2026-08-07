<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelSalaireHistorique extends Model
{
    protected $fillable = [
        'personnel_id',
        'ancien_salaire',
        'nouveau_salaire',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'ancien_salaire' => 'decimal:2',
            'nouveau_salaire' => 'decimal:2',
        ];
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
