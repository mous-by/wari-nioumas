<?php

namespace Database\Factories;

use App\Models\Chauffeur;
use App\Models\Versement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Versement>
 */
class VersementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chauffeur_id' => Chauffeur::factory(),
            'date_versement' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'montant' => fake()->randomElement([10000, 15000, 20000, 25000]),
            'observations' => null,
        ];
    }
}
