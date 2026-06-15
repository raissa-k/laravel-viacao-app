<?php

declare(strict_types=1);

// Testes do fluxo da Busca Pública.
// Cobre: validação de campos obrigatórios e renderização da base.

it('redireciona para a home com erro se faltarem os parâmetros obrigatórios', function () {
    // 1. Acessando sem nenhum parâmetro
    $this->get(route('busca'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');

    // 2. Acessando com parâmetros parciais (faltando a data, por exemplo)
    $this->get(route('busca', [
        'origem'  => 'Curitiba',
        'destino' => 'São Paulo'
    ]))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

it('exibe a base da página de resultados quando os parâmetros estão preenchidos', function () {
    $response = $this->get(route('busca', [
        'origem'  => 'Curitiba',
        'destino' => 'São Paulo',
        'data'    => '2026-06-15',
    ]));

    $response->assertOk()
        ->assertViewIs('buscas.index')
        ->assertViewHas('linhas', function ($linhas) {
            return $linhas instanceof \Illuminate\Support\Collection;
        });
});
it('mantém a URL compartilhável e exibe os parâmetros na view', function () {
    $origem   = 'Curitiba';
    $destino  = 'Ourinhos';

    $response = $this->get(route('busca', [
        'origem'  => $origem,
        'destino' => $destino,
        'data'    => '2026-12-20',
    ]));

    $response->assertOk();

    // Garante que os valores estão sendo impressos no HTML da view corretamente
    $response->assertSee($origem);
    $response->assertSee($destino);
});
