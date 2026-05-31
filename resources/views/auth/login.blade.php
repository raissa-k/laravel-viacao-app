{{-- View de login: usa o layout público.
Compare com src/views/auth/login.php do PHP puro.
$errors->first('email'): erros do Auth::attempt() via withErrors() no controller. --}}
@extends('layouts.public')

@section('title', 'Entrar: Viações Demo')

@section('content')

<div class="auth-wrap">
    <div class="card">

        <h1 class="card-title text-center">Entrar na conta</h1>

        @if ($errors->any())
            {{-- Erros intencionalmente vagos: não dizemos se foi o email ou a senha. Pesquise "user enumeration attack". --}}
            <div class="auth-error">
                <ul class="error-list">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="search-form" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label class="field-label" for="email">E-mail</label>
                {{-- type="email": validação client-side. Sempre valide no servidor também. Um curl ignora qualquer validação client-side. --}}
                <input
                    class="field-input"
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="seu@email.com"
                >
            </div>

            <div class="field">
                <label class="field-label" for="password">Senha</label>
                {{-- NUNCA repopule o campo de senha com o valor enviado. autocomplete="current-password" deixa o browser sugerir a senha salva. --}}
                <input
                    class="field-input"
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
            </div>

            <button class="btn btn-blue" type="submit">Entrar</button>
        </form>

    </div>
</div>

@endsection
