<?php

namespace Database\Factories;

use App\Models\CasSocial;
use App\Models\Personnel;
use App\Models\TypeCasSocial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CasSocial>
 */
class CasSocialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'personnel_id' => Personnel::factory(),
            'type_cas_social_id' => TypeCasSocial::factory(),
            'date_cas' => now()->toDateString(),
            'date_demande' => now()->toDateString(),
            'motif' => fake()->sentence(),
            'montant_demande' => fake()->randomElement([50000, 100000, 150000]),
            'statut' => 'brouillon',
        ];
    }

    public function enAttente(): static
    {
        return $this->state(fn () => ['statut' => 'en_attente']);
    }

    public function approuve(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'approuve',
            'montant_accorde' => $attributes['montant_demande'] ?? 100000,
            'date_validation' => now(),
        ]);
    }
}
