<?php

namespace Database\Factories;

use App\Models\Absence;
use App\Models\Chauffeur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absence>
 */
class AbsenceFactory extends Factory
{
    public function definition(): array
    {
        $debut = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'chauffeur_id' => Chauffeur::factory(),
            'date_debut' => $debut->format('Y-m-d'),
            'date_fin' => $debut->format('Y-m-d'),
            'motif' => fake()->randomElement(['Maladie', 'Raison familiale', 'Panne', 'Congé']),
            'statut' => 'en_attente',
        ];
    }
}
