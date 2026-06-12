@props(['linha' => []])

<article class="linha-card">

    <div class="linha-card-info">
        <?php // Captura de dados com fallback seguro ?>
        <span class="linha-card-numero">{{ data_get($linha, 'numero', 'Linha 0000') }}</span>
        <span class="linha-card-operadora">{{ data_get($linha, 'operadora', 'Viação Exemplo') }}</span>

        <div class="linha-card-meta">
            <?php // Resolução da categoria ?>
            <span class="linha-card-categoria">{{ data_get($linha, 'categoria', 'Convencional') }}</span>
            <span class="linha-card-duracao">{{ data_get($linha, 'duracao', '6h 30min') }}</span>
        </div>
    </div>

    <div class="linha-card-preco">
        <div class="linha-card-preco-text">
            <span class="linha-card-preco-label">a partir de</span>

            <?php // Normalização de preços para Float ?>
            <span class="linha-card-preco-min">R$ {{ number_format(data_get($linha, 'precoMin', 59.90), 2, ',', '.') }}</span>
            @if(data_get($linha, 'precoMax'))
                <span class="linha-card-preco-max">até R$ {{ number_format(data_get($linha, 'precoMax'), 2, ',', '.') }}</span>
            @endif
        </div>

        <a class="linha-card-btn" href="#">Selecionar</a>
    </div>

</article>
