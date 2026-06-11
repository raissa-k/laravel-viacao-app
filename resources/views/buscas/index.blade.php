@extends('layouts.public')

@section('title', 'Passagens de ' . request('origem') . ' para ' . request('destino'))

@section('content')
    <section class="section section-alt">
        <div class="container">

            {{-- Formulário repopulado nativamente devido ao request() no componente --}}
            <div class="mb-lg">
                <x-search-bar layout="horizontal" />
            </div>

            {{-- Cabeçalho promovido a H1, mantendo o estilo discreto e sem o título genérico antigo --}}
            <div class="mb-md mt-lg">
                <h1 class="text-muted" style="font-size: 1rem; font-weight: normal;">
                    Mostrando viagens de <strong>{{ request('origem') }}</strong> para <strong>{{ request('destino') }}</strong> no dia {{ date('d/m/Y', strtotime(request('data'))) }}
                </h1>
            </div>

            {{-- Filtros de Categoria (Client-Side) --}}
            {{-- TODO: Quando o enum categoria estiver disponível, esses filtros deverão ser criados a partir dos cases do Enum. --}}
            <div class="filtros-categoria">
                <button class="filtro-pill" aria-pressed="true" data-filter="todas">Todas</button>
                <button class="filtro-pill" aria-pressed="false" data-filter="convencional">Convencional</button>
                <button class="filtro-pill" aria-pressed="false" data-filter="executivo">Executivo</button>
                <button class="filtro-pill" aria-pressed="false" data-filter="leito">Leito</button>
            </div>

            {{-- Contador de resultados idêntico ao protótipo ("X resultados encontrados") --}}
            <div class="mb-md mt-md text-muted" style="font-size: 0.9rem;">
                <strong>{{ count($linhas) }}</strong> {{ count($linhas) == 1 ? 'resultado encontrado' : 'resultados encontrados' }}
            </div>

            {{-- Lista de Cards --}}
            <div class="grid-auto lista-resultados">
                @forelse ($linhas as $linha)
                    {{-- Mantida a estrutura HTML pura com o atributo data-categoria necessário para o script funcionar --}}
                    <div class="card viacao-card" data-categoria="{{ strtolower($linha->categoria) }}">
                        <strong>{{ $linha->viacao }}</strong>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>Nenhuma viagem encontrada para esta data.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Seção de diferenciais movida para o final da página para espelhar o protótipo fielmente --}}
    <x-diferenciais />

    {{-- Script de filtro do layout public --}}
    @push('scripts')
        <script>
            // Cache das referências (buscadas apenas uma vez no carregamento da página)
            const todosFiltros = document.querySelectorAll('[data-filter]');
            const todosCards = document.querySelectorAll('[data-categoria]');

            // Usando delegação de eventos para não depender de classes CSS
            document.addEventListener('click', (event) => {
                const botaoClicado = event.target.closest('[data-filter]');

                if (!botaoClicado) return;

                // Usando dataset para melhor legibilidade
                const filtroAtivo = botaoClicado.dataset.filter;

                // 1. Volta TODOS os botões para false
                todosFiltros.forEach(btn => btn.setAttribute('aria-pressed', 'false'));

                // 2. Marca o botão atual como true (inclusive o "Todas")
                botaoClicado.setAttribute('aria-pressed', 'true');

                // 3. Aplica o filtro nos cards
                todosCards.forEach(card => {
                    const categoriaCard = card.dataset.categoria;

                    if (filtroAtivo === 'todas' || categoriaCard === filtroAtivo) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        </script>
    @endpush
@endsection
