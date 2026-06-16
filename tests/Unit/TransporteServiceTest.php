<?php

declare(strict_types=1);

use App\Services\TransporteService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    config([
        'services.transporte_api.url'   => 'https://api.test',
        'services.transporte_api.token' => 'token-secreto',
    ]);
});

// — — — listarCidades — — —

it('listarCidades retorna data e meta quando a API responde 200', function () {
    Http::fake([
        'https://api.test/api/cidades*' => Http::response([
            'data' => [['id' => 1, 'nome' => 'Curitiba', 'uf' => 'PR']],
            'meta' => ['last_page' => 1, 'total' => 1],
        ], 200),
    ]);

    $result = (new TransporteService())->listarCidades(1, 10);

    expect($result['data'])->toHaveCount(1)
        ->and($result['data'][0]['nome'])->toBe('Curitiba')
        ->and($result['meta']['last_page'])->toBe(1);
});

it('listarCidades retorna array vazio ao receber HTTP 500', function () {
    Http::fake([
        'https://api.test/api/cidades*' => Http::response([], 500),
    ]);

    $result = (new TransporteService())->listarCidades(1, 10);

    expect($result)->toBe(['data' => [], 'meta' => []]);
});

it('listarCidades retorna array vazio ao lançar ConnectionException', function () {
    Http::fake(function () {
        throw new ConnectionException();
    });

    $result = (new TransporteService())->listarCidades(1, 10);

    expect($result)->toBe(['data' => [], 'meta' => []]);
});

// — — — listarTodasCidades — — —

it('listarTodasCidades pagina corretamente e concatena os resultados', function () {
    Http::fake(function (\Illuminate\Http\Client\Request $request) {
        $page = (int) ($request->data()['page'] ?? 1);

        $data = $page === 1
            ? [['id' => 1, 'nome' => 'Curitiba', 'uf' => 'PR']]
            : [['id' => 2, 'nome' => 'São Paulo', 'uf' => 'SP']];

        return Http::response([
            'data' => $data,
            'meta' => ['last_page' => 2, 'current_page' => $page],
        ], 200);
    });

    $result = (new TransporteService())->listarTodasCidades();

    expect($result)->toHaveCount(2)
        ->and($result[0]['nome'])->toBe('Curitiba')
        ->and($result[1]['nome'])->toBe('São Paulo');

    expect(Http::recorded())->toHaveCount(2);
});

// — — — Token — — —

it('envia o token SHA-256 correto no header Authorization', function () {
    Carbon::setTestNow('2026-06-16 12:00:00');

    Http::fake([
        'https://api.test/api/cidades*' => Http::response([
            'data' => [],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);

    (new TransporteService())->listarCidades(1, 10);

    $esperado = hash('sha256', 'token-secreto:2026-06-16');
    $recorded = Http::recorded();

    expect($recorded[0][0]->header('Authorization')[0])
        ->toBe('Bearer ' . $esperado);

    Carbon::setTestNow();
});
