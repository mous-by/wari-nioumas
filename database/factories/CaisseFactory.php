<?php

namespace Database\Factories;

use App\Models\Caisse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caisse>
 */
class CaisseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'solde_ouverture' => fake()->randomElement([0, 50000, 100000]),
            'date_ouverture' => now(),
            'statut' => 'ouverte',
            'observations' => null,
        ];
    }

    public function fermee(): static
    {
        return $this->state(fn () => [
            'statut' => 'fermee',
            'date_fermeture' => now(),
            'solde_fermeture' => 0,
        ]);
    }
}
