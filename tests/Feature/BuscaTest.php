<?php

declare(strict_types=1);

use App\Actions\FiltrarLinhas;
use App\DTOs\LinhaResultadoDTO;
use App\Models\Cidade;
use App\Services\TransporteService;
use Carbon\Carbon;

// — — — Validação de campos obrigatórios — — —

it('redireciona para home quando nenhum parâmetro é informado', function () {
    $this->get(route('busca'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('redireciona para home quando apenas origem está ausente', function () {
    $this->get(route('busca', ['destino' => '1', 'data' => '2026-06-15']))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('redireciona para home quando apenas destino está ausente', function () {
    $this->get(route('busca', ['origem' => '1', 'data' => '2026-06-15']))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('redireciona para home quando apenas data está ausente', function () {
    $this->get(route('busca', ['origem' => '1', 'destino' => '2']))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

// — — — Validação de cidades no banco — — —

it('exibe a página de resultados para cidades existentes no banco', function () {
    $origem  = Cidade::factory()->create();
    $destino = Cidade::factory()->create();

    $this->get(route('busca', [
        'origem'  => $origem->id,
        'destino' => $destino->id,
        'data'    => Carbon::tomorrow()->format('Y-m-d'),
    ]))
        ->assertViewIs('buscas.index')
        ->assertViewHas('linhas');
});

it('redireciona para home quando a cidade de origem não existe no banco', function () {
    $destino = Cidade::factory()->create();

    $this->get(route('busca', [
        'origem'  => 999,
        'destino' => $destino->id,
        'data'    => Carbon::tomorrow()->format('Y-m-d'),
    ]))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('redireciona para home quando a cidade de destino não existe no banco', function () {
    $origem = Cidade::factory()->create();

    $this->get(route('busca', [
        'origem'  => $origem->id,
        'destino' => 999,
        'data'    => Carbon::tomorrow()->format('Y-m-d'),
    ]))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

// — — — URL compartilhável — — —

it('mantém a URL compartilhável e exibe os nomes das cidades na view', function () {
    $origem  = Cidade::factory()->create(['nome' => 'Curitiba']);
    $destino = Cidade::factory()->create(['nome' => 'Ourinhos']);

    $this->get(route('busca', [
        'origem'  => $origem->id,
        'destino' => $destino->id,
        'data'    => '2026-12-20',
    ]))
        ->assertSee('Curitiba')
        ->assertSee('Ourinhos');
});

it('exibe a estrutura necessária para os filtros e ordenação client-side', function () {

    $origem  = Cidade::factory()->create();
    $destino = Cidade::factory()->create();

    $this->mock(TransporteService::class)->shouldReceive('listarTodasLinhas')->andReturn([]);
    $this->mock(FiltrarLinhas::class)->shouldReceive('execute')->andReturn(LinhaResultadoDTO::fake());//metodo que ja tinha todo um fake preparado.......

    $this->get(route('busca', [
        'origem'  => $origem->id,
        'destino' => $destino->id,
        'data'    => Carbon::tomorrow()->format('Y-m-d'),
    ]))
        ->assertSee('data-filter="todas"', false)
        ->assertSee('data-filter="convencional"', false)
        ->assertSee('data-filter="executivo"', false)
        ->assertSee('data-filter="leito"', false)
        ->assertSee('data-categoria="', false)
        ->assertSee('data-preco-min="', false)
        ->assertSee('data-duracao-min="', false);
});

// — — — show (detalhe da linha) — — —

it('redireciona para home quando data está ausente no show', function () {
    $this->get(route('linhas.show', ['linha' => 1]))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('redireciona para home quando data está no passado no show', function () {
    $this->get(route('linhas.show', ['linha' => 1, 'data' => '2020-01-01']))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('retorna 404 quando a linha não existe na API', function () {
    $this->mock(TransporteService::class)
        ->shouldReceive('buscarLinhaPorId')->andReturn([]);

    $this->get(route('linhas.show', ['linha' => 999, 'data' => Carbon::tomorrow()->format('Y-m-d')]))
        ->assertNotFound();
});

// O teste de renderização de buscas.show entra com a integração da C5-C (DTOs):
// a view espera objetos e o controller passa arrays brutos, conforme o requisito 7.
