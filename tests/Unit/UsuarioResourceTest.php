<?php

use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;

it('expõe apenas os campos esperados', function () {
    $usuario = Usuario::factory()->make();
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado)->toHaveKeys(['id', 'nome', 'email', 'deletado', 'created_at', 'updated_at']);
});

it('não expõe a senha', function () {
    $usuario = Usuario::factory()->make();
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado)->not->toHaveKey('senha');
});

it('não expõe deleted_at', function () {
    $usuario = Usuario::factory()->make();
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado)->not->toHaveKey('deleted_at');
});

it('os valores refletem os dados do model', function () {
    $usuario = Usuario::factory()->make([
        'nome'  => 'João Silva',
        'email' => 'joao@example.com',
    ]);
    $resultado = (new UsuarioResource($usuario))->resolve();

    expect($resultado['nome'])->toBe('João Silva');
    expect($resultado['email'])->toBe('joao@example.com');
});

it('formata created_at como ISO 8601', function () {
    $agora = now();
    $usuario = Usuario::factory()->make(['created_at' => $agora]);
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado['created_at'])
        ->toBeString()
        ->toBe($agora->toIso8601String());
});

it('formata updated_at como ISO 8601', function () {
    $agora = now();
    $usuario = Usuario::factory()->make(['updated_at' => $agora]);
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado['updated_at'])
        ->toBeString()
        ->toBe($agora->toIso8601String());
});

it('retorna null para created_at quando não definido', function () {
    $usuario = Usuario::factory()->make(['created_at' => null]);
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado['created_at'])->toBeNull();
});

it('deletado é false para usuário ativo', function () {
    $usuario = Usuario::factory()->make(['deleted_at' => null]);
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado['deletado'])->toBeFalse();
});

it('deletado é true para usuário excluído', function () {
    $usuario = Usuario::factory()->make(['deleted_at' => now()]);
    $resultado = new UsuarioResource($usuario)->resolve();

    expect($resultado['deletado'])->toBeTrue();
});
