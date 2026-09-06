<?php

namespace Database\Seeders;

use App\Models\TypeCasSocial;
use Illuminate\Database\Seeder;

class TypeCasSocialSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Mariage',
            'Baptême / Naissance',
            'Décès',
            'Maladie / Hospitalisation',
            'Assistance exceptionnelle',
            'Événement familial',
            'Sinistre / difficulté familiale',
            'Autre',
        ] as $libelle) {
            TypeCasSocial::firstOrCreate(['libelle' => $libelle]);
        }
    }
}
