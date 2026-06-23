@extends('layouts.public')

@section('content')
    <x-detalhe-header
        :origem="$origem"
        :destino="$destino"
        origemSubtitulo="Rodoviária"
        destinoSubtitulo="Rodoviária"
        :distanciaKm="$linha->distancia_km ?? '0'"
        precoMinimo="{{ number_format($linha->preco_min ?? 0, 2, ',', '.') }}"
        numero="{{ $linha->numero ?? '0000' }}"
        :categoria="$linha->categoria ?? 'Convencional'"
    />

    <div class="detalhe-container">

        <div class="voltar-container">
            <a href="{{ route('busca', request()->only(['origem', 'destino', 'data'])) }}" class="btn-voltar">
                <svg class="icon-seta" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para Resultados
            </a>
        </div>

        <section class="operadora-card">
            <div class="operadora-item">
                <span class="operadora-label">OPERADORA</span>
                <strong class="operadora-valor text-azul">{{ $linha->viacao->nome ?? 'Auto Viação' }}</strong>
            </div>
            <div class="operadora-item">
                <span class="operadora-label">DURAÇÃO MÉDIA</span>
                <strong class="operadora-valor">{{ isset($linha->duracao_media_min) ? floor($linha->duracao_media_min / 60) . 'h' : '4h' }}</strong>
            </div>
            <div class="operadora-item">
                <span class="operadora-label">DISTÂNCIA</span>
                <strong class="operadora-valor">{{ $linha->distancia_km ?? '0' }} km</strong>
            </div>
            <div class="operadora-item">
                <span class="operadora-label">FAIXA DE PREÇO</span>
                <strong class="operadora-valor">R$ {{ number_format($linha->preco_min ?? 0, 2, ',', '.') }} – R$ {{ number_format($linha->preco_max ?? 0, 2, ',', '.') }}</strong>
            </div>
        </section>

        @if(empty($horarios) || count($horarios) === 0)
            <x-empty-state mensagem="Não há partidas para o dia selecionado." />
        @else
            <x-horario-card :horarios="$horarios" />
        @endif

        <section class="terminais-grid">
            <div class="terminal-card">
                <h3 class="terminal-card-titulo">TERMINAL DE ORIGEM</h3>
                <div class="terminal-card-corpo">
                    <a href="#" class="terminal-cidade-link">{{ $terminalOrigem->nome ?? 'Terminal de Embarque' }}</a>
                    <div class="terminal-info-lista">
                        <p><strong>Telefone:</strong> {{ $terminalOrigem->telefone ?? '(00) 0000-0000' }}</p>
                        <p><strong>Horário de Funcionamento:</strong> {{ $terminalOrigem->horario ?? '24 horas' }}</p>
                        <p><strong>Número de Plataformas:</strong> {{ $terminalOrigem->plataformas ?? '15' }}</p>
                    </div>
                </div>
            </div>

            <div class="terminal-card">
                <h3 class="terminal-card-titulo">TERMINAL DE DESTINO</h3>
                <div class="terminal-card-corpo">
                    <a href="#" class="terminal-cidade-link">{{ $terminalDestino->nome ?? 'Terminal de Desembarque' }}</a>
                    <div class="terminal-info-lista">
                        <p><strong>Telefone:</strong> {{ $terminalDestino->telefone ?? '(00) 0000-0000' }}</p>
                        <p><strong>Horário de Funcionamento:</strong> {{ $terminalDestino->horario ?? '06:00 às 23:00' }}</p>
                        <p><strong>Número de Plataformas:</strong> {{ $terminalDestino->plataformas ?? '12' }}</p>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
