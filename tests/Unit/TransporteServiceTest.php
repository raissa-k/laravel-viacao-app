<?php

declare(strict_types=1);

use App\Services\TransporteService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;


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

    $service   = new TransporteService();
    $resultado = $service->listarLinhas(10, 20);

    expect($resultado['data'])->toHaveCount(1);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/api/linhas')
            && $request['origem_cidade_id']  == 10
            && $request['destino_cidade_id'] == 20;
    });
});

test('listarLinhas retorna array vazio quando api responde 500', function () {
    Http::fake([
        '*' => Http::response([], 500),
    ]);

    $service   = new TransporteService();
    $resultado = $service->listarLinhas(10, 20);

    expect($resultado)->toBe(['data' => [], 'meta' => []]);
});

test('listarLinhas retorna array vazio quando ocorre ConnectionException', function () {
    Http::fake([
        '*' => fn () => throw new ConnectionException('timeout'),
    ]);

    $service   = new TransporteService();
    $resultado = $service->listarLinhas(10, 20);

    expect($resultado)->toBe(['data' => [], 'meta' => []]);
});

test('listarTodasLinhas concatena dados de duas páginas e faz exatamente 2 chamadas', function () {
    $pagina = 0;

    Http::fake([
        '*' => function () use (&$pagina) {
            $pagina++;

            if ($pagina === 1) {
                return Http::response([
                    'data' => [
                        ['id' => 1, 'nome' => 'Linha A'],
                    ],
                    'meta' => ['last_page' => 2],
                ], 200);
            }

            return Http::response([
                'data' => [
                    ['id' => 2, 'nome' => 'Linha B'],
                ],
                'meta' => ['last_page' => 2],
            ], 200);
        },
    ]);

    $service   = new TransporteService();
    $resultado = $service->listarTodasLinhas(10, 20);

    expect($resultado)->toHaveCount(2);
    expect($resultado[0]['id'])->toBe(1);
    expect($resultado[1]['id'])->toBe(2);

    Http::assertSentCount(2);
});
