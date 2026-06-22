@extends('layouts.public')

@section('content')
    <main class="detalhe-container">

        <div style="margin-bottom: 1.5rem;">
            <a href="{{ route('busca', request()->only(['origem', 'destino', 'data'])) }}" class="btn-voltar">
                &larr; Voltar para Resultados
            </a>
        </div>

        <x-detalhe-header
            :origem="request('origem', $linha->origem ?? '')"
            :destino="request('destino', $linha->destino ?? '')"
            :distanciaKm="$linha->distancia ?? null"
            :precoMinimo="$linha->preco_base ?? null"
            :categoria="$linha->categoria ?? null"
            :numero="$linha->id ?? null"
        />

        <div class="detalhe-layout">

            <div class="detalhe-main">

                <section class="bloco-viacao-info">
                    <div class="viacao-header">
                        <div class="viacao-logo-placeholder">
                            {{ strtoupper(substr($linha->viacao->nome ?? 'V', 0, 2)) }}
                        </div>
                        <div>
                            <h2 class="viacao-titulo">{{ $linha->viacao->nome ?? 'Operadora Local' }}</h2>
                            <span class="viacao-subtitulo">Viação responsável pelo trajeto selecionado</span>
                        </div>
                    </div>
                </section>

                <div class="bloco-horarios">
                    <h3 class="bloco-titulo">Horários Disponíveis</h3>

                    @if(empty($horarios) || (is_countable($horarios) && count($horarios) === 0))
                        <x-empty-state mensagem="Não há partidas programadas para esta viação no dia selecionado." />
                    @else
                        <x-horario-card :horarios="$horarios" />
                    @endif
                </div>

            </div>

            <aside class="detalhe-sidebar">

                @if(!empty($terminalOrigem))
                    <div class="bloco-terminal">
                        <div class="terminal-header embarque">
                            <h3 class="terminal-titulo">Terminal de Embarque</h3>
                        </div>
                        <div class="terminal-body">
                            <h4 class="terminal-nome">{{ $terminalOrigem->nome }}</h4>
                            <ul class="terminal-dados">
                                <li>{{ $terminalOrigem->endereco }}</li>
                                <li>{{ $terminalOrigem->cidade }} - {{ $terminalOrigem->uf }}</li>
                                @if(!empty($terminalOrigem->telefone))
                                    <li><strong>Tel:</strong> {{ $terminalOrigem->telefone }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif

                @if(!empty($terminalDestino))
                    <div class="bloco-terminal">
                        <div class="terminal-header desembarque">
                            <h3 class="terminal-titulo">Terminal de Desembarque</h3>
                        </div>
                        <div class="terminal-body">
                            <h4 class="terminal-nome">{{ $terminalDestino->nome }}</h4>
                            <ul class="terminal-dados">
                                <li>{{ $terminalDestino->endereco }}</li>
                                <li>{{ $terminalDestino->cidade }} - {{ $terminalDestino->uf }}</li>
                                @if(!empty($terminalDestino->telefone))
                                    <li><strong>Tel:</strong> {{ $terminalDestino->telefone }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif

            </aside>
        </div>
    </main>
@endsection
