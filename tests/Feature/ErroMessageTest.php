<?php

declare(strict_types=1);

test('vai exibir tela 404 customizada com botao voltar na web', function () {
    $response = $this->get('/rotaQueNaoExiste');

    $response->assertStatus(404);

    $response->assertSee('Voltar para o Início');
    $response->assertSee('Página Não Encontrada');

    $response->assertSee('name="origem"', false);
});

test('vai exibir tela 500 com trace id quando houver erro no servidor', function () {
    $this->app['router']->get('/simularErro500', function () {
        throw new \Exception("Erro simulado no servidor.");
    });

    $response = $this->get('/simularErro500');

    $response->assertStatus(500);
    $response->assertSee('Informe este código ao suporte');

    expect($response->getContent())->toMatch('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/');
});

test('vai retornar estrutura json padronizada para erros de api', function () {
    $response = $this->getJson('/rotaQueNaoExisteNaApi');

    $response->assertStatus(404);

    $response->assertJson([
        'error' => 'Página não encontrada.',
        'code'  => 404,
    ]);
});
