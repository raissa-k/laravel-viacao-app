{{-- View da home pública: hero + grid de viações ativas.
Compare com src/views/home/index.php do PHP puro.
$viacoes aqui é uma Collection Eloquent, não um array, mas @foreach funciona igual.
$v->nome, $v->logo, $v->ativa: mesmas propriedades, agora via Eloquent com cast automático. --}}
@extends('layouts.public')

@section('title', $title)

@section('content')

{{-- HERO SECTION --}}
<section class="hero">
    <div class="container hero-inner">

        {{-- Lado esquerdo: cartão de busca --}}
        <x-search-bar layout="vertical" />

        {{-- Lado direito: texto de chamada --}}
        <div class="flex flex-col gap-sm">
            <p class="hero-eyebrow">🚌 Encontre sua viagem</p>
            <h1 class="hero-title font-black text-white">VAI DE ÔNIBUS</h1>
            <p class="hero-subtitle">
                As melhores viações do Brasil em um só lugar.
            </p>
        </div>

    </div>
</section>

{{-- SEÇÃO DE DIFERENCIAIS --}}
        <x-diferenciais />

{{-- SEÇÃO DE VIAÇÕES --}}
<section class="section section-alt">
    <div class="container">
        <h2 class="text-xl font-bold mb-sm">Viações de Ônibus</h2>
        <p class="text-muted mb-lg">
            {{ $viacoes->count() }} viaç{{ $viacoes->count() !== 1 ? 'ões' : 'ão' }}
            disponíve{{ $viacoes->count() !== 1 ? 'is' : 'l' }} no sistema
        </p>

        @if ($viacoes->isEmpty())
            <div class="empty-state">
                <p>Nenhuma viação cadastrada ainda.</p>
                <a href="{{ route('login') }}">Entrar</a> pra cadastrar a primeira.
            </div>
        @else
            <div class="grid-auto">
                @foreach ($viacoes as $v)
                    <div class="viacao-card">
                        <div class="viacao-logo">
                            @if ($v->logo !== null)
                                <img
                                    src="{{ route('uploads.serve', $v->logo) }}"
                                    alt="Logo da {{ $v->nome }}"
                                >
                            @else
                                {{-- Sem logo: mostra as iniciais. mb_substr respeita UTF-8. --}}
                                <div class="viacao-initials">
                                    {{ strtoupper(mb_substr($v->nome, 0, 2, 'UTF-8')) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-sm w-full">
                            <strong class="viacao-nome">{{ $v->nome }}</strong>
                            <span class="viacao-cidade">📍 {{ $v->cidade?->nome ?? '---' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

@endsection
