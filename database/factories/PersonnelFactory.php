<?php

namespace Database\Factories;

use App\Models\Personnel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Personnel>
 */
class PersonnelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'telephone' => '7'.fake()->numerify('#######'),
            'poste' => fake()->randomElement(['Comptable', 'Caissier', 'Gestionnaire', 'Mécanicien', 'Gardien']),
            'salaire_base' => fake()->randomElement([90000, 120000, 150000, 200000]),
            'banque' => fake()->randomElement(['BDM', 'BOA', 'Ecobank', null]),
            'numero_compte' => fake()->numerify('ML###############'),
            'date_embauche' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'statut' => 'actif',
        ];
    }
}
