{{-- Layout base das páginas administrativas (painel, viações, histórico, etc.)
Compare com src/views/_layout.php do PHP puro.
Diferenças principais:
- @yield / @section substituem a variável $content gerada pelo View::render()
- @auth / @guest substituem o if ($isLogged) com AuthService
- auth()->user() substitui $auth->user()
- {{ }} escapa automaticamente (equivale a htmlspecialchars() do PHP puro)
- route() gera URLs nomeadas em vez de strings hardcoded
- asset() gera URLs de assets com base URL correta (sem filemtime pra cache bust, em produção o Vite/Mix faria isso automaticamente)
--}}
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Viações Admin')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('app.css') }}?v={{ filemtime(public_path('app.css')) }}">
    <link rel="stylesheet" href="{{ asset('admin.css') }}?v={{ filemtime(public_path('admin.css')) }}">
</head>
<body>

<header>
    <nav>
        @auth
            {{-- Usuário logado vê o painel completo --}}
            <a href="{{ route('home') }}">Home</a>
            | <a href="{{ route('viacoes.index') }}">Viações</a>
            | <a href="{{ route('viacoes.create') }}">Nova viação</a>
            | <a href="{{ route('historico.index') }}">Histórico</a>
            | <a href="{{ route('usuarios.index') }}">Usuários</a>
            | <span class="muted">Olá, {{ auth()->user()->nome }}</span>
            | <form class="inline-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Sair</button>
              </form>
        @else
            {{-- Usuário não logado só vê o link de login.
                 Mesma decisão do PHP puro: mostrar links de admin pra quem não está logado é confuso.
                 O middleware protege a rota, mas a UI deve refletir isso também. --}}
            <a href="{{ route('home') }}">Home</a>
            | <a href="{{ route('login') }}">Login</a>
        @endauth
    </nav>
</header>

@include('partials.flash')

<main>
    @yield('content')
</main>

</body>
</html>
