<?php

declare(strict_types=1);

use App\Models\Viacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// 1. Vincula a trait de reset de banco nativa do Laravel/Pest
uses(RefreshDatabase::class);

beforeEach(function () {
    // 2. Força dinamicamente o driver em memória para o CI do GitHub não falhar
    config(['cache.default' => 'array']);

    Cache::clear();
});

test('deve rodar o comando de pre-warming, ignorar viações inválidas e salvar horários no cache', function () {
    // ARRANGEMENT (Preparação dos dados no banco local)
    // Viação válida e sincronizada (Deve ser processada)
    Viacao::factory()->create([
        'nome'   => 'Viação Sul Sincronizada',
        'api_id' => 10,
        'ativa'  => true,
    ]);

    // Viação sem api_id (Deve ser pulada pelo 'continue')
    Viacao::factory()->create([
        'nome'   => 'Viação Local Sem API ID',
        'api_id' => null,
        'ativa'  => true,
    ]);

    // Viação inativa (Deve ser ignorada no escopo do Where)
    Viacao::factory()->create([
        'nome'   => 'Viação Antiga Inativa',
        'api_id' => 20,
        'ativa'  => false,
    ]);

    // MOCKING (Interceptação das chamadas de API externa)
    $apiUrl     = config('services.transporte_api.url');

    Http::fake([
        // Mock da listagem de linhas ativas para a operadora ID 10
        "{$apiUrl}/api/operadoras/10/linhas?status=ativa" => Http::response([
            'data' => [
                ['id' => 901, 'nome' => 'Rota A - Expressa'],
                ['id' => 902, 'nome' => 'Rota B - Convencional'],
            ]
        ], 200),

        // Mock dos horários para a linha 901
        "{$apiUrl}/api/linhas/901/horarios"               => Http::response([
            ['id' => 1, 'horario' => '06:00'],
            ['id' => 2, 'horario' => '12:00']
        ], 200),

        // Mock dos horários para a linha 902
        "{$apiUrl}/api/linhas/902/horarios"               => Http::response([
            ['id' => 3, 'horario' => '18:30']
        ], 200),
    ]);

    // Execução do comando Artisan usando a API do Pest
    $this->artisan('viacao:warm-cache-horarios')
        ->expectsOutput('Iniciando o Pre-Warming do cache de horários...')
        ->expectsOutput('Buscando linhas ativas para a Viação: Viação Sul Sincronizada (API ID: 10)')
        ->expectsOutput('Viação [Viação Local Sem API ID] sem api_id. Pulando...')
        ->assertSuccessful();

    // Verificação do Cache
    // Garante que as chaves dinâmicas blindadas foram criadas com sucesso
    expect(Cache::has('linha:horarios:901'))->toBeTrue()
        ->and(Cache::has('linha:horarios:902'))->toBeTrue();

    // Garante que o conteúdo armazenado está íntegro
    $dadosCache = Cache::get('linha:horarios:901');
    expect($dadosCache)->toBeArray()
        ->and($dadosCache[0]['horario'])->toBe('06:00')
        // Garante que nenhuma chave foi gerada para a operadora inativa ou sem API ID
        ->and(Cache::has('linha:horarios:20'))->toBeFalse();
});
