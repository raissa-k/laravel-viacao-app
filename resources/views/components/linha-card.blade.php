@props(['linha'])

@php
    // Normalização de preços para Float
    preg_match('/(?:(\d+)h)?\s*(?:(\d+)m)?/', $linha->duracao, $m);
    $duracaoMinutos = ((int)($m[1] ?? 0) * 60) + (int)($m[2] ?? 0);
    $classeSlug = strtolower(trim($linha->categoria?->value ?? ''));

    $tipoBadgeExistente = match($classeSlug) {
        'convencional' => 'info',
        'executivo'    => 'warning',
        'leito'        => 'success',
         default        => 'padrao',
    };
@endphp

<article
    class="linha-card"
    data-categoria="{{ $linha->categoria?->value }}"
    data-preco-min="{{ $linha->precoMinimo }}"
    data-duracao-min="{{ $duracaoMinutos }}"
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
                    :tipo="$tipoBadgeExistente"
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

        <a class="linha-card-btn" href="#">Selecionar</a>
    </div>
</article>
