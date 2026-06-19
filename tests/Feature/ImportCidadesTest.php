<?php

declare(strict_types=1);

use App\Models\Cidade;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();                                        //impede que seja realizado requisição real durante os testes
    config([
        'services.transporte_api.url'   => 'https://api.test',
        'services.transporte_api.token' => 'token-secreto',
    ]);
});

it('insere cidades na primeira execução e retorna sucesso', function () {
    Http::fake([
        'https://api.test/api/cidades*' => Http::response([
            'data' => [
                ['id' => 1, 'nome' => 'Curitiba', 'uf' => 'PR'],
                ['id' => 2, 'nome' => 'São Paulo', 'uf' => 'SP'],
            ],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);

    $exitCode = Artisan::call('cidades:importar');

    expect($exitCode)->toBe(0)
        ->and(Cidade::count())->toBe(2);

    $this->assertDatabaseHas('cidades', ['nome' => 'Curitiba', 'uf' => 'PR', 'api_id' => 1]);
    $this->assertDatabaseHas('cidades', ['nome' => 'São Paulo', 'uf' => 'SP', 'api_id' => 2]);

    expect(Artisan::output())->toContain('inseridas=2');
});

it('é idempotente na segunda execução', function () {
    Http::fake([
        'https://api.test/api/cidades*' => Http::response([
            'data' => [
                ['id' => 1, 'nome' => 'Curitiba', 'uf' => 'PR'],
                ['id' => 2, 'nome' => 'São Paulo', 'uf' => 'SP'],
            ],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);

    Artisan::call('cidades:importar');
    Artisan::call('cidades:importar');

    expect(Cidade::count())->toBe(2);
    expect(Artisan::output())->toContain('atualizadas=2');
});

it('retorna falha quando a API não retorna cidades', function () {
    Http::fake([
        'https://api.test/api/cidades*' => Http::response([
            'data' => [],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);


    $exitCode = Artisan::call('cidades:importar');

    expect($exitCode)->toBe(1)
        ->and(Cidade::count())->toBe(0);
});

it('não persiste dados no banco quando a API responde 500', function () {
    Http::fake([
        'https://api.test/api/cidades*' => Http::response([], 500),
    ]);

    $exitCode = Artisan::call('cidades:importar');

    expect($exitCode)->toBe(1)
        ->and(Cidade::count())->toBe(0);
});
