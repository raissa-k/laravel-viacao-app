<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nome'  => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            // Custo reduzido via BCRYPT_ROUNDS=4 no phpunit.xml pra testes rodarem mais rápido.
            'senha' => Hash::make('senha123'),
        ];
    }
}
