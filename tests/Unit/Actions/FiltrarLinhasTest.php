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
