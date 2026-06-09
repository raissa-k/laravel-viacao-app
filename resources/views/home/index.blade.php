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
        <div class="card">
            <h2 class="card-title">Buscar passagem</h2>

            {{-- Form decorativo no demo (não tem backend de busca ainda).
            Mostra como montar um form HTML semântico e acessível.
            Dica: sempre use <label> com "for" = "id" do input. --}}
            <form class="search-form" action="{{ route('home') }}" method="GET">
                <div class="field">
                    <label class="field-label" for="origem">Origem</label>
                    <input
                        class="field-input"
                        type="text"
                        id="origem"
                        name="origem"
                        placeholder="De onde você vai sair?"
                        value="{{ request('origem') }}"
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="destino">Destino</label>
                    <input
                        class="field-input"
                        type="text"
                        id="destino"
                        name="destino"
                        placeholder="Para onde você vai?"
                        value="{{ request('destino') }}"
                    >
                </div>

                <div class="field-row">
                    <div class="field">
                        <label class="field-label" for="data">Data</label>
                        <input
                            class="field-input"
                            type="date"
                            id="data"
                            name="data"
                            value="{{ request('data') }}"
                        >
                    </div>
                    <div class="field">
                        <label class="field-label" for="passageiros">Passageiros</label>
                        <select class="field-input" id="passageiros" name="passageiros">
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" @selected(request('passageiros', '1') == $i)>
                                    {{ $i }} {{ $i === 1 ? 'passageiro' : 'passageiros' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <button class="btn btn-blue" type="submit">Buscar passagem</button>
            </form>
        </div>

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
<section class="diferenciais">
    <div class="container flex justify-center flex-wrap gap-xl">
        <div class="diferencial">
            <span class="diferencial-icon">🛡️</span>
            <div>
                <strong>Viagens seguras</strong>
                <p class="text-muted text-sm">Só viações verificadas e cadastradas</p>
            </div>
        </div>
        <div class="diferencial">
            <span class="diferencial-icon">💳</span>
            <div>
                <strong>Pagamento fácil</strong>
                <p class="text-muted text-sm">Pix, cartão, boleto</p>
            </div>
        </div>
        <div class="diferencial">
            <span class="diferencial-icon">↩️</span>
            <div>
                <strong>Cancelamento</strong>
                <p class="text-muted text-sm">Política clara por viação</p>
            </div>
        </div>
    </div>
</section>

{{-- SEÇÃO DE VIAÇÕES --}}
<section class="section section-alt">
    <div class="container">
        <h2 class="text-xl font-bold mb-sm">Viações de Ônibus</h2>
        <p class="text-muted mb-lg">
            {{ $viacoes->count() }} viação{{ $viacoes->count() !== 1 ? 'ões' : '' }}
            disponível{{ $viacoes->count() !== 1 ? 'eis' : '' }} no sistema
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
