<?php

// Factory de viação: gera dados falsos pra testes e seeds de desenvolvimento.
// Pesquise "Eloquent factories", "Faker library".

namespace Database\Factories;

use App\Models\Viacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Viacao>
 */
class ViacaoFactory extends Factory
{
    protected $model = Viacao::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'cidade' => fake()->city(),
            'ativa' => fake()->boolean(80), // 80% de chance de estar ativa
            'logo' => null,
        ];
    }
}
