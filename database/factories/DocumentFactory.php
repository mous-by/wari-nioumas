<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Vehicule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'documentable_type' => Vehicule::class,
            'documentable_id' => Vehicule::factory(),
            'type_document' => 'assurance',
            'numero' => strtoupper(fake()->bothify('ASS-####')),
            'date_etablissement' => fake()->dateTimeBetween('-1 year', '-1 month'),
            'date_expiration' => fake()->dateTimeBetween('+1 month', '+1 year'),
        ];
    }

    public function expire(): static
    {
        return $this->state(fn () => ['date_expiration' => now()->subDays(5)]);
    }

    public function procheExpiration(): static
    {
        return $this->state(fn () => ['date_expiration' => now()->addDays(3)]);
    }
}
