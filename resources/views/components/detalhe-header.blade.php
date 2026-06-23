@props([
    'origem',
    'destino',
    'origemSubtitulo'  => null,
    'destinoSubtitulo' => null,
    'distanciaKm'      => null,
    'precoMinimo'      => null,
    'numero'           => null,
    'categoria'        => null,
])

<div class="detalhe-header-wrapper">
    <header {{ $attributes->merge(['class' => 'detalhe-header']) }}>
        <div class="detalhe-header-inner">
            <div class="detalhe-header-topo">
                @if ($categoria || $numero)
                    <div class="detalhe-header-meta">
                        @if ($categoria)
                            <x-badge
                                rotulo="{{ ucfirst($categoria) }}"
                                tipo="badge-{{ strtolower($categoria) }}"
                            />
                        @endif
                        @if ($numero)
                            <span class="detalhe-header-numero">Linha {{ $numero }}</span>
                        @endif
                    </div>
                @endif

                @if ($distanciaKm || $precoMinimo)
                    <div class="detalhe-header-preco">
                        @if ($distanciaKm)
                            <span class="detalhe-header-distancia">{{ $distanciaKm }} km</span>
                        @endif
                        @if ($precoMinimo)
                            <span class="detalhe-header-preco-label">a partir de</span>
                            <span class="detalhe-header-preco-valor">R$ {{ $precoMinimo }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="detalhe-header-rotas">
                <div class="detalhe-header-cidade">
                    <span class="detalhe-header-cidade-nome">{{ $origem?->nome ?? 'Origem' }}</span>
                    @if ($origemSubtitulo)
                        <span class="detalhe-header-cidade-sub">{{ $origemSubtitulo }}</span>
                    @endif
                </div>

                <span class="detalhe-header-seta" aria-hidden="true">→</span>

                <div class="detalhe-header-cidade">
                    <span class="detalhe-header-cidade-nome">{{ $destino?->nome ?? 'Destino' }}</span>
                    @if ($destinoSubtitulo)
                        <span class="detalhe-header-cidade-sub">{{ $destinoSubtitulo }}</span>
                    @endif
                </div>
            </div>
        </div>
    </header>
</div>
