<?php

// Testes do CRUD de viações no painel admin.
// Cobre: proteção por auth, listagem, criação, edição, exclusão e validação.

use App\Enums\AcaoHistorico;
use App\Enums\EntidadeHistorico;
use App\Models\Usuario;
use App\Models\Viacao;

// Acesso não autenticado

it('redireciona visitante para login ao acessar o painel', function () {
    $this->get(route('viacoes.index'))->assertRedirect(route('login'));
});

it('redireciona visitante ao tentar criar viação', function () {
    $this->post(route('viacoes.store'), [])->assertRedirect(route('login'));
});

// Listagem

it('exibe a lista de viações para usuário autenticado', function () {
    Viacao::factory()->count(3)->create();

    $this->actingAs(Usuario::factory()->create())
        ->get(route('viacoes.index'))
        ->assertOk()
        ->assertViewIs('admin.viacoes.index')
        ->assertViewHas('viacoes');
});

it('filtra viações por busca textual', function () {
    Viacao::factory()->create(['nome' => 'Cometa', 'cidade' => 'Campinas']);
    Viacao::factory()->create(['nome' => 'Penha',  'cidade' => 'BH']);

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('viacoes.index', ['q' => 'Cometa']));

    $response->assertOk();
    expect($response->viewData('viacoes'))->toHaveCount(1);
});

it('filtra viações por status ativa', function () {
    Viacao::factory()->create(['ativa' => true]);
    Viacao::factory()->create(['ativa' => false]);

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('viacoes.index', ['ativa' => '1']));

    expect($response->viewData('viacoes'))->toHaveCount(1)
        ->and($response->viewData('viacoes')->first()->ativa)->toBeTrue();
});

// Criação

it('exibe o formulário de cadastro', function () {
    $this->actingAs(Usuario::factory()->create())
        ->get(route('viacoes.create'))
        ->assertOk()
        ->assertViewIs('admin.viacoes.create');
});

it('cria viação válida e registra histórico', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user)
        ->post(route('viacoes.store'), [
            'nome' => 'Expresso Teste',
            'cidade' => 'São Paulo',
            'ativa' => true,
        ])
        ->assertRedirect(route('viacoes.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('viacoes', ['nome' => 'Expresso Teste']);
    $this->assertDatabaseHas('historico', ['acao' => 'Criado', 'usuario_id' => $user->id, 'entidade_type' => EntidadeHistorico::Viacao->value]);
});

it('rejeita viação sem nome', function () {
    $this->actingAs(Usuario::factory()->create())
        ->post(route('viacoes.store'), ['nome' => '', 'cidade' => 'SP'])
        ->assertSessionHasErrors('nome');

    $this->assertDatabaseCount('viacoes', 0);
});

it('rejeita viação sem cidade', function () {
    $this->actingAs(Usuario::factory()->create())
        ->post(route('viacoes.store'), ['nome' => 'Viação', 'cidade' => ''])
        ->assertSessionHasErrors('cidade');
});

it('trim nome e cidade antes de validar', function () {
    // prepareForValidation() faz trim, então "  " deve falhar como campo vazio.
    $this->actingAs(Usuario::factory()->create())
        ->post(route('viacoes.store'), ['nome' => '   ', 'cidade' => 'SP'])
        ->assertSessionHasErrors('nome');
});

// Edição

it('exibe o formulário de edição com dados atuais', function () {
    $viacao = Viacao::factory()->create();

    $this->actingAs(Usuario::factory()->create())
        ->get(route('viacoes.edit', $viacao))
        ->assertOk()
        ->assertViewIs('admin.viacoes.edit')
        ->assertViewHas('viacao', $viacao);
});

it('atualiza viação existente e registra histórico', function () {
    $user = Usuario::factory()->create();
    $viacao = Viacao::factory()->create(['nome' => 'Original']);

    $this->actingAs($user)
        ->put(route('viacoes.update', $viacao), [
            'nome' => 'Atualizada',
            'cidade' => $viacao->cidade,
            'ativa' => $viacao->ativa,
        ])
        ->assertRedirect(route('viacoes.show', $viacao));

    $this->assertDatabaseHas('viacoes', ['id' => $viacao->id, 'nome' => 'Atualizada']);
    $this->assertDatabaseHas('historico', ['acao' => 'Editado', 'entidade_id' => $viacao->id, 'entidade_type' => EntidadeHistorico::Viacao->value]);
});

it('retorna 404 ao editar viação inexistente', function () {
    $this->actingAs(Usuario::factory()->create())
        ->get(route('viacoes.edit', 99999))
        ->assertNotFound();
});

// Exclusão

it('remove viação e registra histórico', function () {
    $user = Usuario::factory()->create();

    $viacao = Viacao::factory()->create([
        'ativa' => false,
    ]);

    $this->actingAs($user)
        ->delete(route('viacoes.destroy', $viacao))
        ->assertRedirect(route('viacoes.index'))
        ->assertSessionHas('success');

    $softDeleted = Viacao::withTrashed()->find($viacao->id);

    $this->assertNotEquals(null, $softDeleted->deleted_at);

    $this->assertDatabaseHas('historico', [
        'acao' => AcaoHistorico::Excluido,
        'entidade_id' => $viacao->id,
    ]);
});
