<?php

namespace Database\Factories;

use App\Models\Bulletin;
use App\Models\Personnel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bulletin>
 */
class BulletinFactory extends Factory
{
    public function definition(): array
    {
        return [
            'personnel_id' => Personnel::factory(),
            'periode_mois' => (int) now()->format('n'),
            'periode_annee' => (int) now()->format('Y'),
            'salaire_base' => 120000,
            'primes' => 0,
            'retenues' => 0,
            'statut' => 'brouillon',
        ];
    }
}
