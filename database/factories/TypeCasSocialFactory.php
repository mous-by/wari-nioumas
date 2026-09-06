<?php

namespace Database\Factories;

use App\Models\TypeCasSocial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TypeCasSocial>
 */
class TypeCasSocialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'libelle' => fake()->unique()->randomElement(['Mariage', 'Baptême / Naissance', 'Décès', 'Maladie / Hospitalisation', 'Assistance exceptionnelle']),
            'description' => null,
            'actif' => true,
        ];
    }
}
