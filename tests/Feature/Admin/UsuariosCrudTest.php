<?php

// Exemplos: CRUD de usuários, soft delete, restore e registro no histórico.
// Estes testes são pra servir como exemplo de como usar factories, rotas nomeadas e assertions.

use App\Enums\AcaoHistorico;
use App\Models\Historico;
use App\Models\Usuario;

it('cria, exclui (soft delete) e restaura um usuário, registrando o histórico', function () {
    $actor = Usuario::factory()->create();

    $email = 'intern-test+user@example.test';
    $nome = 'Interno Teste';
    $senha = 'password123';

    $response = $this->actingAs($actor)
        ->post(route('usuarios.store'), [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'senha_confirmation' => $senha,
        ]);

    $response->assertRedirect(route('usuarios.index'));

    $created = Usuario::where('email', $email)->first();
    expect($created)->not->toBeNull();

    // Histórico de criação deve existir e apontar para o ator que criou
    $hc = Historico::where('entidade_id', $created->id)
        ->where('acao', AcaoHistorico::Criado->value)
        ->first();
    expect($hc)->not->toBeNull()
        ->and($hc->usuario_id)->toBe($actor->id)
        ->and($hc->entidade_id)->toBe($created->id);

    // Soft delete via rota
    $response = $this->actingAs($actor)
        ->delete(route('usuarios.destroy', $created));
    $response->assertRedirect(route('usuarios.index'));

    $this->assertSoftDeleted('usuarios', ['id' => $created->id]);

    $he = Historico::where('entidade_id', $created->id)
        ->where('acao', AcaoHistorico::Excluido->value)
        ->first();
    expect($he)->not->toBeNull();

    // A rota de edição não deve encontrar um usuário deletado (binding padrão)
    $this->actingAs($actor)->get(route('usuarios.edit', $created))->assertStatus(404);

    // Restauração via rota
    $response = $this->actingAs($actor)
        ->post(route('usuarios.restore', ['id' => $created->id]));
    $response->assertRedirect(route('usuarios.index'));

    $restored = Usuario::withTrashed()->find($created->id);
    expect($restored)->not->toBeNull()
        ->and($restored->trashed())->toBeFalse();

    $hr = Historico::where('entidade_id', $created->id)
        ->where('acao', AcaoHistorico::Restaurado->value)
        ->first();
    expect($hr)->not->toBeNull();
});
