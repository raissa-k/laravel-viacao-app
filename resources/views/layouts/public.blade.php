{{-- Layout público: páginas que qualquer visitante pode acessar (home, login).
     Compare com src/views/_layout_public.php do PHP puro.
     Mesma estrutura visual, mesma nav com lógica de logado/não-logado.
     Blade substitui o PHP puro: @auth em vez de <?php if ($isLogged): ?>, etc. --}}
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Viações Demo')</title>
    <link rel="stylesheet" href="{{ asset('app.css') }}?v={{ filemtime(public_path('app.css')) }}">
    <link rel="stylesheet" href="{{ asset('home.css') }}?v={{ filemtime(public_path('home.css')) }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="pub-body">

<header class="nav-pub">
    <nav class="container flex items-center justify-between">
        <a href="{{ route('home') }}" class="nav-logo">🚌 Viações</a>

        <div class="flex items-center gap">
            @auth
                <a href="{{ route('viacoes.index') }}" class="nav-link">Painel</a>
                <a href="{{ route('historico.index') }}" class="nav-link">Histórico</a>
                <span class="nav-link">Olá, {{ auth()->user()->nome }}</span>
                <form class="inline-form" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline" type="submit">Sair</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">Entrar</a>
            @endauth
        </div>
    </nav>
</header>

@include('partials.flash')

@yield('content')

<footer class="footer-pub">
    <p>© {{ date('Y') }} Viações Demo</p>
</footer>

@stack('scripts')
</body>
</html>
