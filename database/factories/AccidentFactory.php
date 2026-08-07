<?php

namespace Database\Factories;

use App\Models\Accident;
use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Accident>
 */
class AccidentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicule_id' => Vehicule::factory(),
            'chauffeur_id' => null,
            'date_accident' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'lieu' => fake()->randomElement(['Bamako, Route de Koulikoro', 'Kati', 'Pont des Martyrs', 'Sotuba']),
            'gravite' => fake()->randomElement(array_keys(Accident::GRAVITES)),
            'responsabilite' => fake()->randomElement(array_keys(Accident::RESPONSABILITES)),
            'description' => fake()->sentence(),
            'cout_reparation' => fake()->randomElement([0, 25000, 150000, 500000]),
            'decision' => null,
            'statut' => 'en_cours',
        ];
    }
}
