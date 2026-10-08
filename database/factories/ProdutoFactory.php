<?php

namespace Database\Factories;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produto>
 */
class ProdutoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->words(2, true),
            'valor' => fake()->randomFloat(2, 1, 1000),
            'quantidade' => fake()->numberBetween(1, 100),
            'fora_validade' => false,
        ];
    }
}
