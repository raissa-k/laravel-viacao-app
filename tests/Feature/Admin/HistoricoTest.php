<?php

declare(strict_types=1);

// Testes da listagem do histórico de alterações.
// Cobre: proteção por auth, listagem e filtros (entidade, acao, datas).

use App\Enums\AcaoHistorico;
use App\Enums\EntidadeHistorico;
use App\Models\Historico;
use App\Models\Usuario;
use App\Models\Viacao;

it('redireciona visitante para login ao acessar o histórico', function () {
    $this->get(route('historico.index'))->assertRedirect(route('login'));
});

it('exibe o histórico para usuário autenticado', function () {
    Historico::factory()->count(3)->create();

    $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index'))
        ->assertOk()
        ->assertViewIs('admin.historico.index')
        ->assertViewHas('historico');
});

it('filtra histórico por ação', function () {
    $viacao = Viacao::factory()->create();
    $usuario = Usuario::factory()->create();

    Historico::factory()->criado()->for($viacao, 'entidade')->create();
    Historico::factory()->editado()->for($usuario, 'entidade')->create();

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index', ['acao' => AcaoHistorico::Criado->value]));

    $response->assertOk();
    expect($response->viewData('historico'))->toHaveCount(1)
        ->and($response->viewData('historico')->first()->acao)->toBe(AcaoHistorico::Criado->value);
});

it('filtra histórico por entidade', function () {
    $v1 = Viacao::factory()->create();
    $u1 = Usuario::factory()->create();

    Historico::factory()->criado()->for($v1, 'entidade')->create();
    Historico::factory()->criado()->for($u1, 'entidade')->create();

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index', ['entidade' => EntidadeHistorico::Usuario->value]));

    $response->assertOk();
    expect($response->viewData('historico'))->toHaveCount(1)
        ->and($response->viewData('historico')->first()->entidade_id)->toBe($u1->id);
});

it('ignora acao inválida no filtro', function () {
    Historico::factory()->count(2)->create();

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index', ['acao' => 'valorinvalido']));

    // Valor inválido = sem filtro -> retorna todos
    $response->assertOk();
    expect($response->viewData('historico'))->toHaveCount(2);
});

it('mostra o ator mesmo quando o usuário está excluído', function () {
    $actor = Usuario::factory()->create();
    $actor->delete();

    $viacao = Viacao::factory()->create();
    Historico::factory()->criado()->for($viacao, 'entidade')->create([
        'usuario_id' => $actor->id,
    ]);

    $response = $this->actingAs(Usuario::factory()->create())
        ->get(route('historico.index'));

    $response->assertOk()->assertSee('usuário excluído');
});
