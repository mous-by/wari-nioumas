<?php

namespace Database\Factories;

use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicule>
 */
class VehiculeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'immatriculation' => strtoupper(fake()->unique()->bothify('??-####-??')),
            'marque' => fake()->randomElement(['Toyota', 'Mercedes', 'Renault', 'Hyundai']),
            'modele' => fake()->word(),
            'type' => fake()->randomElement(['Bus', 'Minibus', 'Taxi', 'Camion']),
            'annee' => fake()->numberBetween(2005, (int) date('Y')),
            'etat' => 'actif',
            'observations' => null,
        ];
    }
}
