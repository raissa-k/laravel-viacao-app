<?php

declare(strict_types=1);

use App\Actions\FiltrarLinhas;
use App\DTOs\LinhaResultadoDTO;
use App\Enums\Categoria;

beforeEach(function () {
    $this->action  = new FiltrarLinhas();
    // Passamos ->all() para extrair o array bruto e forçar a Action a converter e lidar com array
    $this->fixture = LinhaResultadoDTO::fake()->all();
});

it('Sem filtros -> todos os 4 itens retornam', function () {
    $resultado = $this->action->execute($this->fixture);

    expect($resultado)->toHaveCount(4)
        ->and($resultado)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

it('$categoria = "convencional" -> 1 item', function () {
    $resultado = $this->action->execute($this->fixture, categoria: 'convencional');

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->numero)->toBe('0202')
        ->and($resultado->first()->categoria)->toBe(Categoria::Convencional);
});

it('Linha com categoria === null não passa no filtro de categoria', function () {
    // Força um filtro qualquer. A linha 0404 (com categoria nula) nunca deve ser retornada.
    $resultado        = $this->action->execute($this->fixture, categoria: 'leito');

    $temCategoriaNula = $resultado->containsStrict('categoria', null);

    expect($temCategoriaNula)->toBeFalse();
});

it('Filtro por dia com comparação case-insensitive protege contra inconsistências', function () {
    // Misturando maiúsculas e minúsculas
    $resultado = $this->action->execute($this->fixture, dia: 'SáBaDo');

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->numero)->toBe('0101');
});

it('Lista vazia nunca passa e valor fora do vocabulário nunca casa (sem lançar exception)', function () {
    // Valor fora do vocabulário
    $resultadoFora   = $this->action->execute($this->fixture, dia: 'feriado');
    expect($resultadoFora)->toBeEmpty();

    // Filtramos um dia normal ('segunda')
    $resultadoValido = $this->action->execute($this->fixture, dia: 'segunda');

    // A linha 0303 e a 0404 têm a lista de dias vazia, logo nunca devem passar aqui
    $listaDeNumeros  = $resultadoValido->pluck('numero')->toArray();
    expect($listaDeNumeros)->not->toContain('0303')
        ->and($listaDeNumeros)->not->toContain('0404');
});

it('Processa e converte arrays brutos corretamente em LinhaResultadoDTO antes de filtrar', function () {
    $arrayBruto = [
        [
            'id'                => 99,
            'numero'            => '9999',
            'operadora_id'      => 1,
            'operadora_nome'    => 'Operadora Bruta',
            'duracao_media_min' => 150,
            'preco_min'         => 50.0,
            'categoria'         => 'semileito',
            'dias_semana'       => ['domingo']
        ]
    ];

    $resultado  = $this->action->execute($arrayBruto, categoria: 'semileito', dia: 'domingo');

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first())->toBeInstanceOf(LinhaResultadoDTO::class)
        ->and($resultado->first()->numero)->toBe('9999');
});

it('resolve o nome das operadoras nos DTOs com uma única query no banco, evitando N+1', function () {
    // 1. Prepara as viações necessárias no banco local
    \App\Models\Viacao::factory()->create(['api_id' => 1, 'nome' => 'Viação Catarinense']);
    \App\Models\Viacao::factory()->create(['api_id' => 2, 'nome' => 'Viação Cometa']);

    $dadosBrutos = [
        ['id' => 10, 'numero' => '1010', 'operadora_id' => 1, 'duracao_media_min' => 120, 'preco_min' => 50.0, 'categoria' => 'executivo', 'dias_semana' => ['segunda']],
        ['id' => 20, 'numero' => '2020', 'operadora_id' => 2, 'duracao_media_min' => 120, 'preco_min' => 60.0, 'categoria' => 'executivo', 'dias_semana' => ['segunda']],
        ['id' => 30, 'numero' => '3030', 'operadora_id' => 1, 'duracao_media_min' => 120, 'preco_min' => 70.0, 'categoria' => 'executivo', 'dias_semana' => ['segunda']], // Repetido
        ['id' => 40, 'numero' => '4040', 'operadora_id' => 9, 'duracao_media_min' => 120, 'preco_min' => 80.0, 'categoria' => 'executivo', 'dias_semana' => ['segunda']], // Fallback
    ];

    // 2. Ativa o log de queries antes de rodar a Action
    \Illuminate\Support\Facades\DB::enableQueryLog();

    // 3. Executa a action
    $resultado = $this->action->execute($dadosBrutos);

    // 4. Coleta as queries que foram executadas
    $queriesExecutadas = \Illuminate\Support\Facades\DB::getQueryLog();
    \Illuminate\Support\Facades\DB::disableQueryLog();

    // 5. Garante apenas 1 query e dados corretos !!!!!!!!!!!!!!!!!
    expect($queriesExecutadas)->toHaveCount(1) // O verdadeiro teste contra N+1 está aqui!
    ->and($resultado)->toHaveCount(4)
        ->and($resultado[0]->operadoraNome)->toBe('Viação Catarinense')
        ->and($resultado[1]->operadoraNome)->toBe('Viação Cometa')
        ->and($resultado[2]->operadoraNome)->toBe('Viação Catarinense')
        ->and($resultado[3]->operadoraNome)->toBe('Operadora Desconhecida');
});
