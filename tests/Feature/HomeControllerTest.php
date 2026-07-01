<?php

declare(strict_types=1);

use App\Models\Cidade;
use App\Models\Viacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () { //aqui passo as configs basicas de conexão com a API e partes obrigatorias antes de executar
    Http::preventStrayRequests();//bloqueio requisições reais e faço um fake das que farei pra teste,e se acontecer de vir uma real,falhe
    config([
        'services.transporte_api.url'   => 'https://api.test',
        'services.transporte_api.token' => 'token-secreto',
    ]);
});

it('GET home responde 200 com os selects de cidades preenchidos;', function () {
    $cidadeA  = Cidade::create([
        'api_id' => 1,
        'nome'   => 'Curitiba',
        'uf'     => 'PR'
    ]);

    Http::fake([
        'https://api.test/*' => Http::response(['status' => 'online'], 200),
    ]);
    $response = $this->get(route('home'));

    $response->assertStatus(200); //verfifique se o status recebido em $response é o mesmo que espero 200
    $response->assertSee($cidadeA->nome);
});

it('GET /busca?... responde 200 com os cards e com o link "Selecionar" apontando para a rota de detalhe', function () {

    $origem     = Cidade::create([ 'api_id' => 12, 'nome' => 'Curitiba', 'uf' => 'PR' ]);

    $destino    = Cidade::create([ 'api_id' => 34, 'nome' => 'Paranaguá', 'uf' => 'PR' ]);

    $dataHoje   = date('Y-m-d');

    Http::fake([ 'https://api.test/*' => Http::response([ 'data' => [ [ 'id' => 10, 'numero' => '1000', 'operadora_id' => 1, 'preco_min' => 45.00, 'dias_semana' => ['segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado', 'domingo'] ], ], ], 200), ]);

    $response   = $this->get("/busca?origem={$origem->id}&destino={$destino->id}&data={$dataHoje}");

    $response->assertStatus(200);

    $response->assertSee('1000');

    $urlDetalhe = route('linhas.show', [ 'linha' => 10, 'origem' => $origem->id, 'destino' => $destino->id, 'data' => $dataHoje ]);

    $response->assertSee('/busca/linhas/10');

    $response->assertSee('data=' . $dataHoje);
});

it('GET no detalhe responde 200 com horários e terminais.', function () {

    $cidadeOrigem  = Cidade::create([ 'api_id' => 12, 'nome' => 'Curitiba', 'uf' => 'PR' ]);

    $cidadeDestino = Cidade::create([ 'api_id' => 34, 'nome' => 'Paranaguá', 'uf' => 'PR' ]);

    $viacao        = Viacao::create([ 'api_id' => 1, 'nome' => 'Viação Graciosa' ]);

    $dataHoje      = date('Y-m-d');

    Http::fake([
        '*/api/linhas/10/horarios' => Http::response([
            'data' => [
                [
                    'id'           => 500,
                    'partida'      => '08:00',
                    'chegada'      => '12:00',
                    'tipo'         => 'convencional',
                    'assentos'     => 40,
                    'diasDaSemana' => []
                ],
            ]
        ], 200),

        '*/api/terminais/100'      => Http::response([
            'data' => [
                'id'            => 100,
                'nome'          => 'rodoferroviária de curitiba',
                'endereco'      => 'av. presidente affonso camargo, 330',
                'telefone'      => '(41) 3320-3000',
                'funcionamento' => '24 horas',
                'servicos'      => []
            ]
        ], 200),

        '*/api/terminais/200'      => Http::response([
            'data' => [
                'id'            => 200,
                'nome'          => 'terminal de paranaguá',
                'endereco'      => 'rua julia da costa, s/n',
                'telefone'      => '(41) 3420-6000',
                'funcionamento' => '06:00 às 23:00',
                'servicos'      => []
            ]
        ], 200),

        '*/api/linhas/10'          => Http::response([
            'data' => [
                'id'                  => 10,
                'numero'              => '1000',
                'operadora_id'        => 1,
                'preco_min'           => 45.00,
                'preco_max'           => 60.00,
                'terminal_origem_id'  => 100,
                'terminal_destino_id' => 200,
            ]
        ], 200),
    ]);

    $response      = $this->get("/busca/linhas/10?data={$dataHoje}&origem={$cidadeOrigem->id}&destino={$cidadeDestino->id}");

    $response->assertStatus(200);

    $response->assertSee('1000');

    $response->assertSee('Viação Graciosa');

    $response->assertSee('Rodoferroviária De Curitiba');

    $response->assertSee('08:00');
});
