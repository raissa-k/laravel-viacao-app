{{-- Formulário de edição de usuário.
Senha é opcional: se deixada em branco, mantém a atual.
O UsuarioRequest usa 'nullable'+'sometimes' pra senha quando editando.
Pesquise "nullable vs sometimes Laravel validation". --}}
@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

<p>
    <a href="{{ route('usuarios.show', $usuario) }}">Ver histórico deste usuário</a>
</p>

@if ($errors->any())
    <div class="alert alert--danger">
        <ul class="error-list">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('usuarios.update', $usuario) }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="nome">Nome</label>
        <input
            type="text"
            id="nome"
            name="nome"
            value="{{ old('nome', $usuario->nome) }}"
            required
            maxlength="255"
        >
    </div>

    <div class="form-group">
        <label for="email">E-mail</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $usuario->email) }}"
            required
            maxlength="255"
        >
    </div>

    <div class="form-group">
        <label for="senha">Nova senha <span class="muted small">(deixe em branco para manter a atual)</span></label>
        <input
            type="password"
            id="senha"
            name="senha"
            autocomplete="new-password"
        >
    </div>

    <div class="form-group">
        <label for="senha_confirmation">Confirmar nova senha</label>
        <input
            type="password"
            id="senha_confirmation"
            name="senha_confirmation"
            autocomplete="new-password"
        >
    </div>

    <div class="form-actions">
        <button type="submit">Salvar alterações</button>
        <a href="{{ route('usuarios.index') }}">Cancelar</a>
    </div>
</form>

@endsection
