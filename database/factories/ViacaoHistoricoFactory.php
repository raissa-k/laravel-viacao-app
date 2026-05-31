<?php

// Factory do histórico de alterações de viações.
// Usada em testes e seeds para gerar registros de historico sem precisar passar por ViacaoService.
//
// Pesquise "Eloquent Factory states", "factory recycle Laravel".

namespace Database\Factories;

use App\Enums\AcaoHistorico;
use App\Models\Usuario;
use App\Models\Viacao;
use App\Models\ViacaoHistorico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ViacaoHistorico>
 */
class ViacaoHistoricoFactory extends Factory
{
    protected $model = ViacaoHistorico::class;

    /*
     * definition() define os valores padrão de cada campo.
     *
     * Por que viacao_id e usuario_id usam factories nested?
     * Quando você chama ViacaoHistorico::factory()->create(),
     * o Laravel percebe que viacao_id = Viacao::factory() e cria uma Viacao automaticamente antes de criar o historico.
     * Isso torna a factory self-contained e faz funcionar mesmo sem viações pré-existentes.
     *
     * Mas quando você QUER usar um viacao/usuario já existente, há opções:
     * ->for($viacao)         -> usa esse model específico (mais explícito)
     * ->for($usuario)        -> idem para usuario
     * ->recycle($collection) -> se a collection tiver uma Viacao, usa uma aleatória dela em vez de criar nova.
     *
     * ->state(['viacao_id' => $id]) define o ID diretamente (útil quando você tem o ID mas não o objeto)
     *
     * Pesquise "for() factory Laravel", "recycle() factory Laravel".
     */
    public function definition(): array
    {
        return [
            'viacao_id' => Viacao::factory(),
            'usuario_id' => Usuario::factory(),
            'acao' => AcaoHistorico::Criado->value,
            'alteracoes' => [
                'before' => null,
                'after' => $this->fakeViacaoSnapshot(),
            ],
        ];
    }

    /*
     * Estado "Criado": before = null (não existia antes), after = snapshot dos dados iniciais.
     * É o padrão, mas ter o estado nomeado deixa o código do seeder/teste mais legível:
     * ViacaoHistorico::factory()->criado()->for($viacao)->create()
     * é mais claro do que só
     * ViacaoHistorico::factory()->for($viacao)->create().
     */
    public function criado(): static
    {
        return $this->state([
            'acao' => AcaoHistorico::Criado->value,
            'alteracoes' => [
                'before' => null,
                'after' => $this->fakeViacaoSnapshot(),
            ],
        ]);
    }

    /*
     * Estado "Editado": before e after contêm APENAS os campos que mudaram (diff parcial).
     * O faker escolhe aleatoriamente qual campo foi editado pra gerar dados realistas.
     *
     * Por que um closure em vez de um array fixo?
     * Sem closure, fake() seria chamado uma vez quando a factory é instanciada e todos os registros teriam o mesmo valor.
     * Com closure (fn()), fake() roda a cada ->create().
     */
    public function editado(): static
    {
        return $this->state(function (array $attrs) {
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

    /*
     * Estado "Excluido": before = snapshot completo (o que existia), after = null (sumiu).
     */
    public function excluido(): static
    {
        return $this->state([
            'acao' => AcaoHistorico::Excluido->value,
            'alteracoes' => [
                'before' => $this->fakeViacaoSnapshot(),
                'after' => null,
            ],
        ]);
    }

    /*
     * Snapshot fake de viação: imita os campos que ViacaoService salva em alteracoes['after'].
     * Mantido como privado pra não duplicar a estrutura em criado() e excluido().
     */
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
