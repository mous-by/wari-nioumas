<?php

namespace Database\Factories;

use App\Models\MandatPaiement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MandatPaiement>
 */
class MandatPaiementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date_mandat' => now()->toDateString(),
            'banque' => fake()->randomElement(['BDM', 'BOA', 'Ecobank']),
            'periode_mois' => (int) now()->format('n'),
            'periode_annee' => (int) now()->format('Y'),
            'montant_total' => 0,
            'statut' => 'brouillon',
        ];
    }
}
