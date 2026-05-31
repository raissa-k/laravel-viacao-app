{{-- View customizada de paginação.
Gerada pelo $items->links() quando Paginator::defaultView() aponta pra este arquivo.
Gera HTML mínimo estilizado pelo .paginacao do admin.css.
Pesquise "Laravel custom pagination view", "Paginator::defaultView". --}}
@if ($paginator->hasPages())
    <nav class="paginacao-nav" role="navigation" aria-label="Paginação">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="paginacao-item paginacao-item-disabled">&#8592; Anterior</span>
        @else
            <a class="paginacao-item" href="{{ $paginator->previousPageUrl() }}" rel="prev">&#8592; Anterior</a>
        @endif

        {{-- Números de página --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="paginacao-item paginacao-item-disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="paginacao-item paginacao-item-atual" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="paginacao-item" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Próxima --}}
        @if ($paginator->hasMorePages())
            <a class="paginacao-item" href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima &#8594;</a>
        @else
            <span class="paginacao-item paginacao-item-disabled">Próxima &#8594;</span>
        @endif
    </nav>
@endif
