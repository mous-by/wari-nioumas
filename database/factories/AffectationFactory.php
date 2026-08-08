<?php

namespace Database\Factories;

use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Affectation>
 */
class AffectationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicule_id' => Vehicule::factory(),
            'chauffeur_id' => Chauffeur::factory(),
            'montant_journalier' => fake()->randomElement([10000, 12500, 15000]),
            'periodicite' => 'journalier',
            'date_debut' => fake()->dateTimeBetween('-1 year', 'now'),
            'date_fin' => null,
            'motif_fin' => null,
            'observations' => null,
        ];
    }
}
