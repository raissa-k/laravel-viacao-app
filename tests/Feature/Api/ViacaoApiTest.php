<?php

// Testes da API REST de viações.
// No Laravel + Sanctum: Sanctum::actingAs() faz o middleware auth:sanctum reconhecer o usuário sem emitir um token real.
// Pesquise "Sanctum::actingAs", "Laravel HTTP tests", "assertJson".

use App\Models\Usuario;
use App\Models\Viacao;
use Laravel\Sanctum\Sanctum;

// Rotas públicas

it('lista viações sem autenticação', function () {
    Viacao::factory()->count(3)->create();

    // viações podem ter sido criadas inativas e não vão aparecer na listagem
    $countActive = Viacao::where('ativa', true)->count();

    $this->getJson('/api/viacoes')
        ->assertOk()
        ->assertJsonStructure(['ok', 'count', 'data'])
        ->assertJsonPath('ok', true)
        ->assertJsonPath('count', $countActive);
});

it('retorna viação por ID', function () {
    $viacao = Viacao::factory()->create(['nome' => 'Cometa']);

    $this->getJson("/api/viacoes/{$viacao->id}")
        ->assertOk()
        ->assertJsonPath('data.nome', 'Cometa');
});

it('retorna 404 para viação inexistente', function () {
    $this->getJson('/api/viacoes/99999')
        ->assertNotFound()
        ->assertJsonPath('ok', false);
});

// Rotas protegidas, testes sem autenticação

it('rejeita criação sem token com 401', function () {
    $this->postJson('/api/viacoes', ['nome' => 'Teste', 'cidade' => 'SP'])
        ->assertUnauthorized();
});

it('rejeita atualização sem token com 401', function () {
    $viacao = Viacao::factory()->create();

    $this->putJson("/api/viacoes/{$viacao->id}", ['nome' => 'X', 'cidade' => 'Y'])
        ->assertUnauthorized();
});

it('rejeita exclusão sem token com 401', function () {
    $viacao = Viacao::factory()->create();

    $this->deleteJson("/api/viacoes/{$viacao->id}")
        ->assertUnauthorized();
});

// Rotas protegidas com Sanctum

it('cria viação com token Sanctum e retorna 201 com recurso completo', function () {
    Sanctum::actingAs(Usuario::factory()->create());

    $this->postJson('/api/viacoes', [
        'nome' => 'Nova Viação',
        'cidade' => 'Curitiba',
        'ativa' => true,
    ])
        ->assertCreated()                          // 201
        ->assertJsonPath('ok', true)
        ->assertJsonStructure(['data' => ['id', 'nome', 'cidade', 'ativa']]);

    $this->assertDatabaseHas('viacoes', ['nome' => 'Nova Viação']);
});

it('valida campos obrigatórios na criação', function () {
    Sanctum::actingAs(Usuario::factory()->create());

    $this->postJson('/api/viacoes', [])
        ->assertUnprocessable()                    // 422
        ->assertJsonValidationErrors(['nome', 'cidade']);
});

it('atualiza viação com token Sanctum', function () {
    Sanctum::actingAs(Usuario::factory()->create());
    $viacao = Viacao::factory()->create(['nome' => 'Original']);

    $this->putJson("/api/viacoes/{$viacao->id}", [
        'nome' => 'Atualizada',
        'cidade' => $viacao->cidade,
    ])
        ->assertOk()
        ->assertJsonPath('data.nome', 'Atualizada');

    $this->assertDatabaseHas('viacoes', ['id' => $viacao->id, 'nome' => 'Atualizada']);
});

it('retorna 404 ao atualizar viação inexistente', function () {
    Sanctum::actingAs(Usuario::factory()->create());

    $this->putJson('/api/viacoes/99999', ['nome' => 'X', 'cidade' => 'Y'])
        ->assertNotFound();
});

it('remove viação com token Sanctum e retorna 204', function () {
    Sanctum::actingAs(Usuario::factory()->create());
    $viacao = Viacao::factory()->create();

    $this->deleteJson("/api/viacoes/{$viacao->id}")
        ->assertNoContent();                       // 204

    $this->assertDatabaseMissing('viacoes', ['id' => $viacao->id]);
});

it('retorna 404 ao excluir viação inexistente', function () {
    Sanctum::actingAs(Usuario::factory()->create());

    $this->deleteJson('/api/viacoes/99999')
        ->assertNotFound();
});
