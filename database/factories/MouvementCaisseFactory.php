<?php

namespace Database\Factories;

use App\Models\Caisse;
use App\Models\MouvementCaisse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MouvementCaisse>
 */
class MouvementCaisseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'caisse_id' => Caisse::factory(),
            'type' => fake()->randomElement(['entree', 'sortie']),
            'libelle' => fake()->sentence(3),
            'montant' => fake()->randomElement([5000, 10000, 25000]),
            'date_mouvement' => now()->toDateString(),
        ];
    }
}
