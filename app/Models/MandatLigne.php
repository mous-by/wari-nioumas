<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MandatLigne extends Model
{
    protected $fillable = [
        'mandat_paiement_id',
        'personnel_id',
        'bulletin_id',
        'montant',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
        ];
    }

    public function mandat()
    {
        return $this->belongsTo(MandatPaiement::class, 'mandat_paiement_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function bulletin()
    {
        return $this->belongsTo(Bulletin::class);
    }
}
