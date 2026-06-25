<?php

declare(strict_types=1);

// Cria um stdClass com as propriedades que o template de horario-card lê.
// Após C5-B o template usa acesso a objeto ($h->partida, $h->dias, etc.),
// não mais acesso a array ($h['partida']).
function horarioFake(array $overrides = []): object
{
    return (object) array_merge([
        'partida'   => '08:00',
        'chegada'   => '12:00',
        'categoria' => null,
        'assentos'  => null,
        'dias'      => [],
        'preco'     => null,
        'precoMax'  => null,
    ], $overrides);
}

// — — — x-detalhe-header — — —
// Após C5-B o componente renderiza $origem?->nome e $destino?->nome,
// então os testes passam objetos com ->nome (não strings puras).

it('renderiza origem e destino no cabeçalho de detalhe', function () {
    $origem  = (object) ['nome' => 'Curitiba'];
    $destino = (object) ['nome' => 'São Paulo'];

    $this->blade(
        '<x-detalhe-header :origem="$origem" :destino="$destino" />',
        compact('origem', 'destino')
    )
        ->assertSee('Curitiba')
        ->assertSee('São Paulo');
});

it('renderiza campos opcionais quando informados no cabeçalho', function () {
    $origem  = (object) ['nome' => 'Londrina'];
    $destino = (object) ['nome' => 'Maringá'];

    $this->blade(
        '<x-detalhe-header :origem="$origem" :destino="$destino" :categoria="$categoria" :numero="$numero" :distanciaKm="$distanciaKm" :precoMinimo="$precoMinimo" />',
        [
            'origem'      => $origem,
            'destino'     => $destino,
            'categoria'   => 'executivo',  // string — template faz ucfirst($categoria)
            'numero'      => '1234',
            'distanciaKm' => 98,
            'precoMinimo' => '45,50',      // a view pré-formata antes de passar ao componente
        ]
    )
        ->assertSee('Londrina')
        ->assertSee('Maringá')
        ->assertSee('Executivo')
        ->assertSee('Linha 1234')
        ->assertSee('98 km')
        ->assertSee('45,50');
});

it('não dispara exception ao omitir todos os campos opcionais do cabeçalho', function () {
    $origem  = (object) ['nome' => 'A'];
    $destino = (object) ['nome' => 'B'];

    expect(fn () => $this->blade(
        '<x-detalhe-header :origem="$origem" :destino="$destino" />',
        compact('origem', 'destino')
    ))->not->toThrow(\Throwable::class);
});

// — — — x-horario-card — — —

it('renderiza partida e chegada no card de horário', function () {
    $this->blade('<x-horario-card :horarios="$horarios" />', [
        'horarios' => [
            horarioFake(['partida' => '08:00', 'chegada' => '12:30']),
        ],
    ])
        ->assertSee('08:00')
        ->assertSee('12:30')
        ->assertSee('partida')
        ->assertSee('chegada');
});

it('exibe a contagem correta de horários no summary', function () {
    $this->blade('<x-horario-card :horarios="$horarios" />', [
        'horarios' => [
            horarioFake(['partida' => '06:00', 'chegada' => '10:00']),
            horarioFake(['partida' => '14:00', 'chegada' => '18:00']),
        ],
    ])
        ->assertSee('2');
});

it('renderiza um badge por dia da semana informado', function () {
    $view = $this->blade('<x-horario-card :horarios="$horarios" />', [
        'horarios' => [
            horarioFake(['dias' => ['segunda', 'sexta']]),
        ],
    ]);

    expect(substr_count((string) $view, 'badge-dias-da-semana'))->toBe(2);
});

// — — — Dataset: dados sujos no campo dias — — —
// Após C5-B o template itera $h->dias (objeto), não $h['dias'] (array).
// O dataset passa objetos stdClass para cada caso inválido.

it('degrada sem exception para dados sujos no campo dias', function (object $horario) {
    expect(fn () => $this->blade('<x-horario-card :horarios="$horarios" />', [
        'horarios' => [$horario],
    ]))->not->toThrow(\Throwable::class);
})->with([
    'array vazio'       => [(object) ['partida' => '08:00', 'chegada' => '12:00', 'dias' => []]],
    'null'              => [(object) ['partida' => '08:00', 'chegada' => '12:00', 'dias' => null]],
    'string feriado'    => [(object) ['partida' => '08:00', 'chegada' => '12:00', 'dias' => 'feriado']],
    'chave inexistente' => [(object) ['partida' => '08:00', 'chegada' => '12:00']],
]);
