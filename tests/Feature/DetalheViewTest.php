<?php

declare(strict_types=1);

it('view de detalhe exibe nome da operadora, horários e terminais sem o controller', function () {
    // $origem/$destino chegam ao componente x-detalhe-header, que lê ->nome
    $origem          = (object) ['nome' => 'Curitiba'];
    $destino         = (object) ['nome' => 'São Paulo'];

    $linha           = (object) [
        'numero'            => '1050',
        'distancia_km'      => 408,
        'preco_min'         => 45.50,
        'preco_max'         => 89.90,
        'duracao_media_min' => 300,
        'categoria'         => 'executivo',
    ];

    $terminalOrigem  = (object) ['nome' => 'Rodoviária de Curitiba'];
    $terminalDestino = (object) ['nome' => 'Rodoviária de São Paulo'];

    $horario         = (object) [
        'partida'   => '08:00',
        'chegada'   => '12:30',
        'categoria' => null,
        'assentos'  => null,
        'dias'      => [],
        'preco'     => null,
        'precoMax'  => null,
    ];

    $this->view('buscas.show', [
        'linha'           => $linha,
        'horarios'        => collect([$horario]),
        'terminalOrigem'  => $terminalOrigem,
        'terminalDestino' => $terminalDestino,
        'origem'          => $origem,
        'destino'         => $destino,
        'nomeOperadora'   => 'Cometa',
        'cidades'         => collect([]),
    ])
        ->assertSee('Cometa')
        ->assertSee('Rodoviária de Curitiba')
        ->assertSee('Rodoviária de São Paulo')
        ->assertSee('08:00')
        ->assertSee('12:30')
        ->assertSee('Horários disponíveis');
});

it('view de detalhe exibe empty-state quando não há horários', function () {
    $origem  = (object) ['nome' => 'Curitiba'];
    $destino = (object) ['nome' => 'São Paulo'];

    $linha   = (object) [
        'numero'       => '1050',
        'distancia_km' => 408,
        'preco_min'    => 45.50,
        'preco_max'    => 89.90,
        'categoria'    => 'executivo',
    ];

    $this->view('buscas.show', [
        'linha'           => $linha,
        'horarios'        => collect([]),
        'terminalOrigem'  => (object) ['nome' => 'Terminal A'],
        'terminalDestino' => (object) ['nome' => 'Terminal B'],
        'origem'          => $origem,
        'destino'         => $destino,
        'nomeOperadora'   => 'Cometa',
        'cidades'         => collect([]),
    ])
        ->assertDontSee('Horários disponíveis')
        ->assertSee('Nada encontrado');
});
