<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasSocialDocument extends Model
{
    use HasFactory;

    public const TYPES = [
        'faire_part' => 'Faire-part',
        'certificat' => 'Certificat',
        'acte' => 'Acte',
        'demande_ecrite' => 'Demande écrite',
        'justificatif_medical' => 'Justificatif médical',
        'decision_direction' => 'Décision de la direction',
        'recu' => 'Reçu',
        'autre' => 'Autre document',
    ];

    protected $fillable = [
        'cas_social_id',
        'type_document',
        'chemin',
        'nom_original',
        'uploaded_by',
    ];

    public function casSocial()
    {
        return $this->belongsTo(CasSocial::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeDocumentLibelleAttribute(): string
    {
        return self::TYPES[$this->type_document] ?? $this->type_document;
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->chemin);
    }

    public function getCheminPathAttribute(): string
    {
        return storage_path('app/public/'.$this->chemin);
    }
}
