<?php

declare(strict_types=1);

use App\DTOs\HorarioResultadoDTO;
use App\Enums\Categoria;

test('deve instanciar HorarioResultadoDTO mapeando os dados com sucesso e formatando as horas com Carbon', function () {
    $dadosBrutos = [
        'id'                    => 45,
        'partida'               => '14:35:00',
        'chegada_estimada'      => '18:10:00',
        'tipo'                  => 'leito',
        'assentos'              => 32,
        'diasDaSemana'          => ['Segunda', 'Quarta'],
        'preco_min'             => 140.50,
        'preco_max'             => 210.00,
    ];

    $dto         = HorarioResultadoDTO::fromArray($dadosBrutos, 50.00, 100.00);

    expect($dto->id)->toBe(45)
        ->and($dto->partida)->toBe('14:35')
        ->and($dto->chegada)->toBe('18:10')
        ->and($dto->categoria)->toBe(Categoria::Leito)
        ->and($dto->assentos)->toBe(32)
        ->and($dto->diasDaSemana)->toBe(['Segunda', 'Quarta'])
        ->and($dto->precoMinimo)->toBe(140.50)
        ->and($dto->precoMaximo)->toBe(210.00);
});

test('deve aplicar os fallbacks de preço da linha pai e categoria padrão se a API omitir os dados', function () {
    $dadosBrutos = [
        'id'               => 10,
        'partida'          => '08:00',
        'chegada_estimada' => '11:00',
    ];

    $dto         = HorarioResultadoDTO::fromArray($dadosBrutos, 75.90, null);

    expect($dto->categoria)->toBe(Categoria::Convencional)
        ->and($dto->precoMinimo)->toBe(75.90)
        ->and($dto->precoMaximo)->toBeNull()
        ->and($dto->assentos)->toBe(0)
        ->and($dto->diasDaSemana)->toBe([]);
});

test('deve validar fallbacks críticos de hora ausente mantendo os dias da semana fornecidos', function () {
    $dadosBrutos = [
        'diasDaSemana' => ['Sábado', 'Domingo'],
    ];

    $dto         = HorarioResultadoDTO::fromArray($dadosBrutos, 0.0, null);

    expect($dto->partida)->toBe('00:00')
        ->and($dto->chegada)->toBe('00:00')
        ->and($dto->diasDaSemana)->toBe(['Sábado', 'Domingo']);
});
