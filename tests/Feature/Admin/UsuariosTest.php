<?php

// Testes da listagem de usuários (somente leitura).

use App\Models\Usuario;

it('redireciona visitante para login ao acessar usuários', function () {
    $this->get(route('usuarios.index'))->assertRedirect(route('login'));
});

it('exibe a lista de usuários para autenticado', function () {
    Usuario::factory()->count(3)->create();

    $this->actingAs(Usuario::factory()->create())
        ->get(route('usuarios.index'))
        ->assertOk()
        ->assertViewIs('admin.usuarios.index')
        ->assertViewHas('usuarios');
});

it('filtra usuários por busca textual', function () {
    Usuario::factory()->create(['nome' => 'Carlos Silva']);
    Usuario::factory()->create(['nome' => 'Ana Lima']);

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('usuarios.index', ['q' => 'Carlos']));

    $response->assertOk();
    expect($response->viewData('usuarios'))->toHaveCount(1);
});
