@php
    // Captura de dados com fallback seguro
    $numero    = data_get($linha, 'numero', 'Linha 0000');
    $operadora = data_get($linha, 'operadoraNome', data_get($linha, 'operadora', 'Viação Exemplo'));
    $duracao   = data_get($linha, 'duracao', '6h 30min');

    // Normalização de preços para Float
    $precoMinRaw = data_get($linha, 'precoMin', data_get($linha, 'preco_min', 59.90));
    $precoMaxRaw = data_get($linha, 'precoMax', data_get($linha, 'preco_max', 89.90));

    $vMin = is_numeric($precoMinRaw) ? (float) $precoMinRaw : (float) str_replace(',', '.', $precoMinRaw);
    $vMax = is_numeric($precoMaxRaw) ? (float) $precoMaxRaw : (float) str_replace(',', '.', $precoMaxRaw);

    $precoMinExibicao = number_format($vMin, 2, ',', '.');
    $precoMaxExibicao = number_format($vMax, 2, ',', '.');

    // Resolução da categoria
    $categoriaInput = data_get($linha, 'categoria');

    $categoriaExibicao = is_object($categoriaInput) && method_exists($categoriaInput, 'rotulo')
        ? $categoriaInput->rotulo()
        : ($categoriaInput ?? 'convencional');

    $dataCategoria = is_object($categoriaInput) && isset($categoriaInput->value)
        ? $categoriaInput->value
        : strtolower(trim($categoriaExibicao));
@endphp

<article {{ $attributes->merge(['class' => 'linha-card', 'data-categoria' => $dataCategoria]) }}>

    <div class="linha-card-info">
        <span class="linha-card-numero">{{ $numero }}</span>
        <span class="linha-card-operadora">{{ $operadora }}</span>

        <div class="linha-card-meta">
            <span class="linha-card-categoria">{{ ucfirst($categoriaExibicao) }}</span>
            <span class="linha-card-duracao">{{ $duracao }}</span>
        </div>
    </div>

    <div class="linha-card-preco">
        <span class="linha-card-preco-label">a partir de</span>
        <span class="linha-card-preco-min">R$ {{ $precoMinExibicao }}</span>

        @if($vMax > $vMin)
            <span class="linha-card-preco-max">até R$ {{ $precoMaxExibicao }}</span>
        @endif

        <a href="#" class="btn btn-blue linha-card-btn">Selecionar</a>
    </div>

</article>
