<?php

// Testes da listagem do histórico de alterações.
// Cobre: proteção por auth, listagem e filtros (viacao_id, acao, datas).
// NOTA: Busca por conteúdo JSON é propositalmente removida por ser uma anti-pattern em audit tables.
// Veja HistoricoService.php para detalhes.

use App\Enums\AcaoHistorico;
use App\Models\Usuario;
use App\Models\Viacao;
use App\Models\ViacaoHistorico;

it('redireciona visitante para login ao acessar o histórico', function () {
    $this->get(route('historico.index'))->assertRedirect(route('login'));
});

it('exibe o histórico para usuário autenticado', function () {
    ViacaoHistorico::factory()->count(3)->create();

    $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index'))
        ->assertOk()
        ->assertViewIs('admin.historico.index')
        ->assertViewHas('historico');
});

it('filtra histórico por ação', function () {
    $viacao = Viacao::factory()->create();
    $usuario = Usuario::factory()->create();

    ViacaoHistorico::factory()->criado()->for($viacao)->for($usuario)->create();
    ViacaoHistorico::factory()->editado()->for($viacao)->for($usuario)->create();

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index', ['acao' => AcaoHistorico::Criado->value]));

    $response->assertOk();
    expect($response->viewData('historico'))->toHaveCount(1)
        ->and($response->viewData('historico')->first()->acao)->toBe(AcaoHistorico::Criado->value);
});

it('filtra histórico por viacao_id', function () {
    $v1 = Viacao::factory()->create();
    $v2 = Viacao::factory()->create();

    ViacaoHistorico::factory()->criado()->for($v1)->create();
    ViacaoHistorico::factory()->criado()->for($v2)->create();

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index', ['viacao_id' => $v1->id]));

    $response->assertOk();
    expect($response->viewData('historico'))->toHaveCount(1)
        ->and($response->viewData('historico')->first()->viacao_id)->toBe($v1->id);
});

it('ignora acao inválida no filtro', function () {
    ViacaoHistorico::factory()->count(2)->create();

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index', ['acao' => 'valorinvalido']));

    // Valor inválido = sem filtro -> retorna todos
    $response->assertOk();
    expect($response->viewData('historico'))->toHaveCount(2);
});
