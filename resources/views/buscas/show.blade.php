@extends('layouts.public')

@section('content')
    <div class="detalhe-container">

        <div class="voltar-container">
            <a href="{{ route('busca', request()->only(['origem', 'destino', 'data'])) }}" class="btn-voltar">
                <svg class="icon-seta" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para Resultados
            </a>
        </div>

        <x-detalhe-header
            :origem="$origem"
            :destino="$destino"
            origemSubtitulo="Rodoviária"
            destinoSubtitulo="Rodoviária"
            :distanciaKm="$linha->distancia ?? '300'"
            precoMinimo="59.90"
            numero="0606"
            :categoria="isset($horarios[0]) ? $horarios[0]->categoria : null"
        />

        <section class="operadora-card">
            <div class="operadora-item">
                <span class="operadora-label">OPERADORA</span>
                <strong class="operadora-valor text-azul">{{ $linha->viacao->nome ?? null }}</strong>
            </div>
            <div class="operadora-item">
                <span class="operadora-label">DURAÇÃO MÉDIA</span>
                <strong class="operadora-valor">{{ $linha->duracao_estimada ?? null }}</strong>
            </div>
            <div class="operadora-item">
                <span class="operadora-label">DISTÂNCIA</span>
                <strong class="operadora-valor">{{ $linha->distancia ?? null }} km</strong>
            </div>
            <div class="operadora-item">
                <span class="operadora-label">FAIXA DE PREÇO</span>
                <strong class="operadora-valor">R$ 59,90 – R$ 89,90</strong>
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
