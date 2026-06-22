@props(['linha'])

<article
    {{ $attributes->class('linha-card') }}
>
    <div class="linha-card-info">
        {{-- Captura de dados com fallback seguro --}}
        <span class="linha-card-numero">{{ $linha->numero }}</span>
        <span class="linha-card-operadora">{{ $linha->operadoraNome }}</span>

        <div class="linha-card-meta">
            {{-- Resolução da categoria usando as badges existentes do sistema --}}
            @if($linha->categoria)
                <x-badge
                    :rotulo="$linha->categoria->rotulo()"
                    :tipo="$linha->categoria->tipoBadge()"
                />
            @endif
            <span class="linha-card-duracao">{{ $linha->duracao }}</span>
        </div>
    </div>

    <div class="linha-card-preco">
        <div class="linha-card-preco-text">
            <span class="linha-card-preco-label">a partir de</span>
            <span class="linha-card-preco-min">R$ {{ number_format($linha->precoMinimo, 2, ',', '.') }}</span>

            @if($linha->precoMaximo !== null)
                <span class="linha-card-preco-max">até R$ {{ number_format($linha->precoMaximo, 2, ',', '.') }}</span>
            @endif
        </div>

        <a class="linha-card-btn" href="{{ route('linhas.show', ['linha' => $linha->id, 'data' => request('data')]) }}">Selecionar</a>
    </div>
</article>
