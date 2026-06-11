@php
    // Captura de dados com fallback seguro
    $numero    = data_get($linha, 'numero', 'Linha 0000');
    $operadora = data_get($linha, 'operadoraNome', data_get($linha, 'operadora', 'Viação Exemplo'));
    $duracao   = data_get($linha, 'duracao', '6h 30min');

    // Normalização de preços para Float
    $precoMin = data_get($linha, 'precoMin', data_get($linha, 'preco_min', 59.90));
    $precoMax = data_get($linha, 'precoMax', data_get($linha, 'preco_max', null));

    // Resolução da categoria
    $categoriaInput = data_get($linha, 'categoria', 'convencional');

    $dataCategoria = strtolower(trim($categoriaInput));
@endphp

<article {{ $attributes->merge(['class' => 'linha-card', 'data-categoria' => $dataCategoria]) }}>

    <div class="linha-card-info">
        <span class="linha-card-numero">{{ $numero }}</span>
        <span class="linha-card-operadora">{{ $operadora }}</span>

        <div class="linha-card-meta">
            <span class="linha-card-categoria">{{ ucfirst($categoriaInput) }}</span>
            <span class="linha-card-duracao">{{ $duracao }}</span>
    </div>
    </div>

    <div class="linha-card-preco">
        <span class="linha-card-preco-label">a partir de</span>
        <span class="linha-card-preco-min">R$ {{ number_format($precoMin, 2, ',', '.') }}</span>

        @if($precoMax)
            <span class="linha-card-preco-max">até R$ {{ number_format($precoMax, 2, ',', '.') }}</span>
        @endif

        <a href="#" class="btn btn-blue linha-card-btn">Selecionar</a>
    </div>

</article>
