<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voyage extends Model
{
    use HasFactory;

    protected $fillable = [
        'affectation_id',
        'date_voyage',
        'montant',
        'observations',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_voyage' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
