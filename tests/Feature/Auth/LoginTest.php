<?php

declare(strict_types=1);

// Testes de autenticação: login e logout.
// No Laravel: actingAs(), post(), assertAuthenticatedAs() abstraem a mecânica de sessão.

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

it('exibe o formulário de login', function () {
    $this->get('/login')
        ->assertOk()
        ->assertViewIs('auth.login');
});

it('redireciona usuário já autenticado para fora do login', function () {
    $this->actingAs(Usuario::factory()->create())
        ->get('/login')
        ->assertRedirect();
});

it('autentica com credenciais válidas e redireciona para o painel', function () {
    $user = Usuario::factory()->create(['senha' => Hash::make('senha123')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'senha123'])
        ->assertRedirect(route('viacoes.index'));

    $this->assertAuthenticatedAs($user);
});

it('rejeita senha incorreta com erro vago', function () {
    // Erro vago intencional: não diz se foi email ou senha (user enumeration).
    $user = Usuario::factory()->create(['senha' => Hash::make('senha123')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'errada'])
        ->assertRedirect()
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('valida formato de email no login', function () {
    $this->post('/login', ['email' => 'nao-e-um-email', 'password' => 'qualquer'])
        ->assertSessionHasErrors('email');
});

it('valida campos obrigatórios no login', function () {
    $this->post('/login', [])
        ->assertSessionHasErrors(['email', 'password']);
});

it('realiza logout e invalida a sessão', function () {
    $this->actingAs(Usuario::factory()->create())
        ->post('/logout')
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
