<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AcaoHistorico;
use App\Enums\EntidadeHistorico;
use App\Models\Historico;
use App\Models\Usuario;
use App\Models\Viacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Historico>
 */
class HistoricoFactory extends Factory
{
    protected $model = Historico::class;

    /*
     * Por padrão cria histórico de uma viação.
     * Use ->for($model, 'entidade') nos testes para apontar para qualquer entidade polimórfica (Viacao ou Usuario):
     * Historico::factory()->for($viacao, 'entidade')->create()
     * Historico::factory()->for($usuario, 'entidade')->create()
     *
     * O Laravel resolve entidade_type e entidade_id automaticamente via morph map registrado em AppServiceProvider.
     * Pesquise "Factory for() morphTo", "morph map Laravel".
     */
    public function definition(): array
    {
        return [
            // Usamos ->value para que o alias gravado no banco venha sempre do enum, não de uma string avulsa.
            'entidade_type' => EntidadeHistorico::Viacao->value,
            'entidade_id' => Viacao::factory(),
            'usuario_id' => Usuario::factory(),
            'acao' => AcaoHistorico::Criado->value,
            'alteracoes' => [
                'before' => null,
                'after' => $this->fakeViacaoSnapshot(),
            ],
        ];
    }

    public function criado(): static
    {
        return $this->state([
            'acao' => AcaoHistorico::Criado->value,
            'alteracoes' => ['before' => null, 'after' => $this->fakeViacaoSnapshot()],
        ]);
    }

    public function editado(): static
    {
        return $this->state(function () {
            $campo = fake()->randomElement(['nome', 'cidade', 'ativa']);

            [$before, $after] = match ($campo) {
                'nome' => [['nome' => fake()->company()], ['nome' => fake()->company()]],
                'cidade' => [['cidade' => fake()->city()],  ['cidade' => fake()->city()]],
                'ativa' => [['ativa' => false],            ['ativa' => true]],
            };

            return [
                'acao' => AcaoHistorico::Editado->value,
                'alteracoes' => ['before' => $before, 'after' => $after],
            ];
        });
    }

    public function excluido(): static
    {
        return $this->state([
            'acao' => AcaoHistorico::Excluido->value,
            'alteracoes' => ['before' => $this->fakeViacaoSnapshot(), 'after' => null],
        ]);
    }

    private function fakeViacaoSnapshot(): array
    {
        return [
            'nome' => fake()->company(),
            'cidade' => fake()->city(),
            'ativa' => fake()->boolean(80),
            'logo' => null,
        ];
    }
}
