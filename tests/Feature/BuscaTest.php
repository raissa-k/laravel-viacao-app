<?php

declare(strict_types=1);

use App\Models\Cidade;

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
        'data'    => '2026-06-15',
    ]))
        ->assertOk()
        ->assertViewIs('buscas.index')
        ->assertViewHas('linhas', []);
});

it('redireciona para home quando a cidade de origem não existe no banco', function () {
    $destino = Cidade::factory()->create();

    $this->get(route('busca', [
        'origem'  => 999,
        'destino' => $destino->id,
        'data'    => '2026-06-15',
    ]))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('redireciona para home quando a cidade de destino não existe no banco', function () {
    $origem = Cidade::factory()->create();

    $this->get(route('busca', [
        'origem'  => $origem->id,
        'destino' => 999,
        'data'    => '2026-06-15',
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
        ->assertOk()
        ->assertSee('Curitiba')
        ->assertSee('Ourinhos');
});
