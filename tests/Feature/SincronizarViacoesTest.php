<?php

declare(strict_types=1);

use App\Models\Viacao;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    config([
        'services.transporte_api.url'   => 'https://api.test',
        'services.transporte_api.token' => 'token-secreto',
    ]);
});

it('retorna falha e banco intocado quando a API responde 500', function () {
    Http::fake([
        'https://api.test/api/operadoras*' => Http::response([], 500),
    ]);

    $exitCode = Artisan::call('viacoes:sincronizar');

    expect($exitCode)->toBe(1)
        ->and(Viacao::count())->toBe(0);
});

it('retorna falha quando a API retorna lista vazia', function () {
    Http::fake([
        'https://api.test/api/operadoras*' => Http::response([
            'data' => [],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);

    $exitCode = Artisan::call('viacoes:sincronizar');

    expect($exitCode)->toBe(1)
        ->and(Viacao::count())->toBe(0);
});

it('insere operadoras e retorna sucesso', function () {
    Http::fake([
        'https://api.test/api/operadoras*' => Http::response([
            'data' => [
                ['id' => 10, 'nome' => 'Viação Teste', 'site' => null, 'sede_cidade_id' => null, 'ativo' => true],
            ],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);

    $exitCode = Artisan::call('viacoes:sincronizar');

    expect($exitCode)->toBe(0)
        ->and(Viacao::count())->toBe(1);
});
