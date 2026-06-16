<?php

declare(strict_types=1);

use App\Services\TransporteService;

$service = new TransporteService();

test('lista linhas com sucesso', function () {

    Http::fake([
        '*' => Http::response([
            'data' => [
                [
                    'id' => 1,
                    'nome' => 'Linha A',
                ]
            ],
            'meta' => [
                'last_page' => 1,
            ],
        ], 200),
    ]);

    $service = new TransporteService();

    $resultado = $service->listarLinhas(
        10,
        20
    );

    expect($resultado['data'])
        ->toHaveCount(1);



});
