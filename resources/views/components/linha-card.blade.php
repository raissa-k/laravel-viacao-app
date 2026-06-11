@props(['linha'])

{{-- O atributo data-categoria é fundamental para a filtragem JS --}}
<div class="card viacao-card" data-categoria="{{ strtolower($linha->categoria) }}">
    <div class="flex justify-between items-center mb-sm">
        <strong class="text-lg">{{ $linha->viacao }}</strong>
        <span class="badge" style="background: #eee; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; text-transform: capitalize;">
            {{ $linha->categoria }}
        </span>
    </div>

    <div class="flex justify-between items-center my-md">
        <div class="flex flex-col">
            <span class="text-muted text-sm">Saída</span>
            <strong>{{ $linha->saida }}</strong>
            <small>{{ $linha->origem }}</small>
        </div>
        <div>
            <span class="text-muted">➔</span>
        </div>
        <div class="flex flex-col text-right">
            <span class="text-muted text-sm">Chegada</span>
            <strong>{{ $linha->chegada }}</strong>
            <small>{{ $linha->destino }}</small>
        </div>
    </div>

    <div class="flex justify-between items-center mt-md pt-sm" style="border-top: 1px solid #eee;">
        <span class="text-xl font-bold" style="color: var(--color-primary)">
            R$ {{ number_format($linha->preco, 2, ',', '.') }}
        </span>
        <button class="btn btn-primary btn-sm">Selecionar</button>
    </div>
</div>
