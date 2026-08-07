<?php

namespace Database\Factories;

use App\Models\Depense;
use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Depense>
 */
class DepenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicule_id' => Vehicule::factory(),
            'categorie' => fake()->randomElement(array_keys(Depense::CATEGORIES)),
            'montant' => fake()->randomElement([5000, 15000, 25000, 50000, 75000]),
            'date_depense' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'description' => null,
        ];
    }
}
