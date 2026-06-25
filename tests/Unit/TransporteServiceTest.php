<?php

declare(strict_types=1);

use App\Services\TransporteService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

test('lista linhas com sucesso', function () {

    Http::fake([
        '*' => Http::response([
            'data' => [
                [
                    'id'   => 1,
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
    $pagina    = 0;

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

beforeEach(function () {
    Http::preventStrayRequests();
    Cache::flush();
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

    $result = new TransporteService()->listarCidades(1, 10);

    expect($result['data'])->toHaveCount(1)
        ->and($result['data'][0]['nome'])->toBe('Curitiba')
        ->and($result['meta']['last_page'])->toBe(1);
});

it('listarCidades retorna array vazio ao receber HTTP 500', function () {
    Http::fake([
        'https://api.test/api/cidades*' => Http::response([], 500),
    ]);

    $result = new TransporteService()->listarCidades(1, 10);

    expect($result)->toBe(['data' => [], 'meta' => []]);
});

it('listarCidades retorna array vazio ao lançar ConnectionException', function () {
    Http::fake(function () {
        throw new ConnectionException();
    });

    $result = new TransporteService()->listarCidades(1, 10);

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

    $result = new TransporteService()->listarTodasCidades();

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

    new TransporteService()->listarCidades(1, 10);

    $esperado = hash('sha256', 'token-secreto:2026-06-16');
    $recorded = Http::recorded();

    expect($recorded[0][0]->header('Authorization')[0])
        ->toBe('Bearer ' . $esperado);

    Carbon::setTestNow();
});

//--listar operadoras--

it('listarOperadoras retorna data e meta quando a API responde 200', function () { //teste de sucesso
    Http::fake([
        'https://api.test/api/operadoras*' => Http::response([
            'data' => [['id' => 1, 'nome' => 'Operadora A']],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);

    $result = new TransporteService()->listarOperadoras(1, 10);

    expect($result['data'])->toHaveCount(1)
        ->and($result['meta']['last_page'])->toBe(1);
});

it('listarOperadoras retorna array vazio ao receber HTTP 500', function () { //teste 2
    Http::fake([
        'https://api.test/api/operadoras*' => Http::response([], 500),
    ]);

    $result = new TransporteService()->listarOperadoras(1, 10);

    expect($result)->toBe(['data' => [], 'meta' => []]);
});

it('listarOperadoras retorna array vazio ao lançar ConnectionException', function () { //teste 3
    Http::fake(function () {
        throw new ConnectionException();
    });

    $result = new TransporteService()->listarOperadoras(1, 10);

    expect($result)->toBe(['data' => [], 'meta' => []]);
});

it('listarTodasOperadoras retorna os dados unificados quando a API responde 200', function () { //teste 1
    Http::fake([
        'https://api.test/api/operadoras*' => Http::response([
            'data' => [['id' => 1, 'nome' => 'Operadora A']],
            'meta' => ['last_page' => 1],
        ], 200),
    ]);

    $result = new TransporteService()->listarTodasOperadoras();

    expect($result)->toHaveCount(1)
        ->and($result[0]['nome'])->toBe('Operadora A');
});

it('listarTodasOperadoras retorna array vazio ao receber HTTP 500', function () { //teste 500 ou 2
    Http::fake([
        'https://api.test/api/operadoras*' => Http::response([], 500),
    ]);

    $result = new TransporteService()->listarTodasOperadoras();

    expect($result)->toBeEmpty();
});

it('listarTodasOperadoras retorna array vazio ao lançar ConnectionException', function () { //teste 3
    Http::fake(function () {
        throw new ConnectionException();
    });

    $result = new TransporteService()->listarTodasOperadoras();

    expect($result)->toBeEmpty();
});

it('listarTodasOperadoras pagina corretamente com closure fake e concatena os resultados', function () { //teste de closure
    Http::fake(function (\Illuminate\Http\Client\Request $request) {
        $page = (int) ($request->data()['page'] ?? 1);

        $data = $page === 1
            ? [['id' => 1, 'nome' => 'Operadora Pag 1']]
            : [['id' => 2, 'nome' => 'Operadora Pag 2']];

        return Http::response([
            'data' => $data,
            'meta' => ['last_page' => 2, 'current_page' => $page],
        ], 200);
    });

    $result = new TransporteService()->listarTodasOperadoras();

    expect($result)->toHaveCount(2)
        ->and($result[0]['nome'])->toBe('Operadora Pag 1')
        ->and($result[1]['nome'])->toBe('Operadora Pag 2');

    Http::assertSentCount(2);
});


// — — — buscarTerminal (cache) — — —

it('buscarTerminal retorna os dados do terminal quando a API responde 200', function () {
    Http::fake([
        'https://api.test/api/terminais/*' => Http::response([
            'data' => ['id' => 1, 'nome' => 'Rodoviária de Curitiba'],
        ], 200),
    ]);

    $result = new TransporteService()->buscarTerminal(1);

    expect($result['data']['nome'])->toBe('Rodoviária de Curitiba');
});

it('buscarTerminal usa o cache na segunda chamada e bate na API só uma vez', function () {
    Http::fake([
        'https://api.test/api/terminais/*' => Http::response([
            'data' => ['id' => 1, 'nome' => 'Rodoviária de Curitiba'],
        ], 200),
    ]);

    $service  = new TransporteService();

    $primeira = $service->buscarTerminal(1);
    $segunda  = $service->buscarTerminal(1);

    expect($segunda)->toBe($primeira);

    // prova do "caminho curto": a API foi chamada uma única vez
    Http::assertSentCount(1);
});

it('buscarTerminal não cacheia erro e tenta a API de novo no request seguinte', function () {
    Http::fake([
        'https://api.test/api/terminais/*' => Http::sequence()
            ->push([], 500)                                        // 1ª: erro
            ->push(['data' => ['id' => 1, 'nome' => 'OK']], 200),  // 2ª: sucesso
    ]);

    $service  = new TransporteService();

    $primeira = $service->buscarTerminal(1);
    $segunda  = $service->buscarTerminal(1);

    expect($primeira)->toBe(['data' => []])           // erro = fallback vazio
    ->and($segunda['data']['nome'])->toBe('OK');  // não veio do cache: bateu na API de novo

    Http::assertSentCount(2); // prova que o erro NÃO grudou no cache
});

it('buscarTerminal loga o cache hit na segunda chamada', function () {
    Http::fake([
        'https://api.test/api/terminais/*' => Http::response([
            'data' => ['id' => 1, 'nome' => 'Rodoviária de Curitiba'],
        ], 200),
    ]);

    $service = new TransporteService();

    $service->buscarTerminal(1); // miss: popula o cache

    Log::spy();                       // a partir daqui, observa o Log

    $service->buscarTerminal(1); // hit: deve logar debug

    Log::shouldHaveReceived('debug')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return str_contains($message, 'cache hit')
                && $context['id'] === 1;
        });
});

it('buscarTerminalSemCache sempre bate na API e ignora o cache', function () {
    Http::fake([
        'https://api.test/api/terminais/*' => Http::response([
            'data' => ['id' => 1, 'nome' => 'Terminal Fresco'],
        ], 200),
    ]);

    $service = new TransporteService();

    $service->buscarTerminalSemCache(1);
    $service->buscarTerminalSemCache(1);

    Http::assertSentCount(2); // duas chamadas = nunca usou cache
});

// — — — buscarLinhaPorId — — —

it('buscarLinhaPorId retorna dados da linha quando a API responde 200', function () {
    Http::fake([
        'https://api.test/api/linhas/1' => Http::response([
            'id'   => 1,
            'nome' => 'Linha Direta - Curitiba/SP',
        ], 200),
    ]);

    $result = new TransporteService()->buscarLinhaPorId(1);

    expect($result)->toHaveKey('id', 1)
        ->and($result)->toHaveKey('nome', 'Linha Direta - Curitiba/SP');
});

it('buscarLinhaPorId retorna array vazio documentando falha ao buscar quando a API responde 404 (não existe)', function () {
    Http::fake([
        'https://api.test/api/linhas/999' => Http::response([
            'message' => 'Not Found'
        ], 404),
    ]);

    $result = new TransporteService()->buscarLinhaPorId(999);

    // Documentado: deve retornar fallback vazio caso falhe em encontrar
    expect($result)->toBe([]);
});

it('buscarLinhaPorId retorna array vazio ao lançar ConnectionException', function () {
    Http::fake(function () {
        throw new ConnectionException();
    });

    $result = new TransporteService()->buscarLinhaPorId(1);

    expect($result)->toBe([]);
});

// — — — listarHorariosDaLinha — — —

it('listarHorariosDaLinha retorna os horários quando a API responde 200', function () {
    Http::fake([
        'https://api.test/api/linhas/1/horarios' => Http::response([
            'data' => [
                ['id' => 1, 'saida' => '08:00', 'chegada' => '12:00']
            ]
        ], 200),
    ]);

    $result = new TransporteService()->listarHorariosDaLinha(1);

    expect($result['data'])->toHaveCount(1)
        ->and($result['data'][0]['saida'])->toBe('08:00');
});

it('listarHorariosDaLinha retorna array vazio sem lançar exception quando a API responde 500', function () {
    Http::fake([
        'https://api.test/api/linhas/1/horarios' => Http::response([], 500),
    ]);

    $result = new TransporteService()->listarHorariosDaLinha(1);

    // Assegura que em vez de exception, temos a supressão e devolução de fallback limpo.
    expect($result)->toBe([]);
});

it('listarHorariosDaLinha retorna array vazio sem disparar exceção em caso de timeout', function () {
    Http::fake([
        'https://api.test/api/linhas/1/horarios' => fn () => throw new ConnectionException('timeout'),
    ]);

    $result = new TransporteService()->listarHorariosDaLinha(1);

    expect($result)->toBe([]);
});

// — — — dois ids diferentes chaves de cache independentes — — —

it('buscarTerminal faz 2 chamadas HTTP para 2 ids diferentes pois as chaves de cache são independentes', function () {
    Http::fake([
        'https://api.test/api/terminais/1' => Http::response(['data' => ['id' => 1, 'nome' => 'Terminal A']], 200),
        'https://api.test/api/terminais/2' => Http::response(['data' => ['id' => 2, 'nome' => 'Terminal B']], 200),
    ]);

    $service = new TransporteService();
    $service->buscarTerminal(1);
    $service->buscarTerminal(2);

    Http::assertSentCount(2);
});

// — — — Cache::shouldReceive() — chave e TTL exato — — —

it('buscarTerminal invoca Cache com a chave terminal:{id} e TTL de exatamente 60 minutos', function () {
    Carbon::setTestNow('2026-06-22 10:00:00');

    Http::fake([
        'https://api.test/api/terminais/42' => Http::response(
            ['data' => ['id' => 42, 'nome' => 'Terminal Fake']],
            200
        ),
    ]);

    Cache::shouldReceive('has')
        ->with('terminal:42')
        ->once()
        ->andReturn(false);

    Cache::shouldReceive('put')
        ->with(
            'terminal:42',
            Mockery::any(),
            Mockery::on(fn ($ttl) => $ttl->eq(Carbon::now()->addHour()))
        )
        ->once();

    new TransporteService()->buscarTerminal(42);

    Carbon::setTestNow();
});

// — — — Time Travel — expiração do TTL — — —

it('buscarTerminal consulta a API de novo após o TTL de 1 hora expirar', function () {
    Http::fake([
        'https://api.test/api/terminais/1' => Http::response(['data' => ['id' => 1, 'nome' => 'Terminal']], 200),
    ]);

    $service = new TransporteService();

    $service->buscarTerminal(1);   // cache miss → popula (1ª req)

    $this->travel(61)->minutes();  // avança além do TTL de 60 min

    $service->buscarTerminal(1);   // expirado → consulta API (2ª req)

    Http::assertSentCount(2);
});
