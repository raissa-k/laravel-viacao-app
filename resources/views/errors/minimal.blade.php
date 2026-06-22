@extends('errors.layout.blade')

@section('title', $title ?? ('Erro ' . ($code ?? '')))

@section('content')
    <div class="error-page error-page--{{ $code ?? 'generic' }}">
        <div class="error-page__container">
            <p class="error-page__code">@yield('code')</p>
            <h1 class="error-page__heading">@yield('title')</h1>
            <p class="error-page__message">@yield('message')</p>

            @yield('error-content')

            @hasSection('error-content')
            @else
                <a href="{{ route('viacoes.index') }}" class="btn btn--primary error-page__back">
                    Voltar ao início
                </a>
            @endif
        </div>
    </div>
@endsection
