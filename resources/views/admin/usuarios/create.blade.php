{{-- Formulário de cadastro de usuário.
Padrão idêntico ao viacoes/create.blade.php: @csrf, old(), @error().
senha_confirmation: Laravel valida automaticamente quando a regra 'confirmed' está ativa.
Pesquise "Laravel password confirmation", "confirmed validation rule". --}}
@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

@if ($errors->any())
    <div class="alert alert--danger">
        <ul class="error-list">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('usuarios.store') }}">
    @csrf

    <div class="form-group">
        <label for="nome">Nome</label>
        <input
            type="text"
            id="nome"
            name="nome"
            value="{{ old('nome') }}"
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
            value="{{ old('email') }}"
            required
            maxlength="255"
        >
    </div>

    <div class="form-group">
        <label for="senha">Senha</label>
        <input
            type="password"
            id="senha"
            name="senha"
            required
            autocomplete="new-password"
        >
    </div>

    <div class="form-group">
        <label for="senha_confirmation">Confirmar senha</label>
        <input
            type="password"
            id="senha_confirmation"
            name="senha_confirmation"
            required
            autocomplete="new-password"
        >
    </div>

    <div class="form-actions">
        <button type="submit">Salvar usuário</button>
        <a href="{{ route('usuarios.index') }}">Cancelar</a>
    </div>
</form>

@endsection
