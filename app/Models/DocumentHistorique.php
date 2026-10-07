<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentHistorique extends Model
{
    protected $fillable = [
        'document_id',
        'ancienne_echeance',
        'nouvelle_echeance',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'ancienne_echeance' => 'date',
            'nouvelle_echeance' => 'date',
        ];
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
