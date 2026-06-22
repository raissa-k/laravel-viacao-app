<?php

declare(strict_types=1);

use App\Enums\Categoria;

// — — — x-detalhe-header — — —

it('renderiza origem e destino no cabeçalho de detalhe', function () {
    $this->blade('<x-detalhe-header origem="Curitiba" destino="São Paulo" />')
        ->assertSee('Curitiba')
        ->assertSee('São Paulo');
});

it('renderiza campos opcionais quando informados no cabeçalho', function () {
    $this->blade(
        '<x-detalhe-header :origem="$origem" :destino="$destino" :categoria="$categoria" :numero="$numero" :distanciaKm="$distanciaKm" :precoMinimo="$precoMinimo" />',
        [
            'origem'      => 'Londrina',
            'destino'     => 'Maringá',
            'categoria'   => Categoria::Executivo,
            'numero'      => '1234',
            'distanciaKm' => 98,
            'precoMinimo' => 45.50,
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
    expect(fn () => $this->blade('<x-detalhe-header origem="A" destino="B" />'))
        ->not->toThrow(\Throwable::class);
});

// — — — x-horario-card — — —

it('renderiza partida e chegada no card de horário', function () {
    $this->blade('<x-horario-card :horarios="$horarios" />', [
        'horarios' => [
            ['partida' => '08:00', 'chegada' => '12:30'],
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
            ['partida' => '06:00', 'chegada' => '10:00'],
            ['partida' => '14:00', 'chegada' => '18:00'],
        ],
    ])
        ->assertSee('2');
});

it('renderiza um badge por dia da semana informado', function () {
    $view = $this->blade('<x-horario-card :horarios="$horarios" />', [
        'horarios' => [
            ['partida' => '08:00', 'chegada' => '12:00', 'dias' => ['segunda', 'sexta']],
        ],
    ]);

    expect(substr_count((string) $view, 'badge-dias-da-semana'))->toBe(2);
});

// — — — Dataset: dados sujos no campo dias — — —

it('degrada sem exception para dados sujos no campo dias', function (array $horario) {
    expect(fn () => $this->blade('<x-horario-card :horarios="$horarios" />', [
        'horarios' => [$horario],
    ]))->not->toThrow(\Throwable::class);
})->with([
    'array vazio'       => [['partida' => '08:00', 'chegada' => '12:00', 'dias' => []]],
    'null'              => [['partida' => '08:00', 'chegada' => '12:00', 'dias' => null]],
    'string feriado'    => [['partida' => '08:00', 'chegada' => '12:00', 'dias' => 'feriado']],
    'chave inexistente' => [['partida' => '08:00', 'chegada' => '12:00']],
]);
