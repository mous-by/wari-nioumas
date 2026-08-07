<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicule_id' => Vehicule::factory(),
            'chauffeur_id' => null,
            'date_incident' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'type' => fake()->randomElement(array_keys(Incident::TYPES)),
            'gravite' => fake()->randomElement(array_keys(Incident::GRAVITES)),
            'description' => fake()->sentence(),
            'cout' => fake()->randomElement([0, 10000, 30000, 75000]),
            'decision' => null,
            'statut' => 'ouvert',
        ];
    }
}
