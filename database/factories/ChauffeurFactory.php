<?php

namespace Database\Factories;

use App\Models\Chauffeur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chauffeur>
 */
class ChauffeurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'matricule' => 'CH-'.fake()->unique()->numerify('####'),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'telephone' => fake()->unique()->numerify('7#######'),
            'adresse' => fake()->city(),
            'nina' => 'NINA'.fake()->unique()->numerify('#######'),
            'permis_numero' => 'PC-'.fake()->unique()->numerify('#####'),
            'permis_date_validite' => fake()->dateTimeBetween('+6 months', '+3 years'),
            'date_embauche' => fake()->dateTimeBetween('-5 years', 'now'),
            'statut' => 'actif',
            'observations' => null,
        ];
    }
}
