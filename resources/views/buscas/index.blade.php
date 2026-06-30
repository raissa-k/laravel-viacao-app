@extends('layouts.public')

@section('title', 'Passagens de ' . $origem->nome . ' para ' . $destino->nome)

@section('content')
    {{-- Faixa azul com a search bar, espelhando o protótipo da imagem 1 --}}
    <div class="bg-primary">
        <div class="container">
            <x-search-bar layout="horizontal"
                          :cidades="$cidades"
                          :action="route('busca')"
            />
        </div>
    </div>

    <section class="section section-alt">
        <div class="container">

            {{-- Cabeçalho promovido a H1, mantendo o estilo discreto e sem o título genérico antigo --}}
            <div class="mb mt-sm">
                <h1 class="text-muted h1-discreto">
                    Mostrando viagens de <strong>{{ $origem->nome }}</strong> para <strong>{{ $destino->nome }}</strong> no dia {{ date('d/m/Y', strtotime(request('data'))) }}
                </h1>
            </div>

            {{-- Filtros de Categoria (Client-Side) --}}
            <div class="filtros-categoria">
                <button class="filtro-pill" aria-pressed="true" data-filter="todas">Todas</button>

                @foreach (\App\Enums\Categoria::cases() as $categoria)
                    <button class="filtro-pill" aria-pressed="false" data-filter="{{ $categoria->value }}">
                        {{ $categoria->rotulo() }}
                    </button>
                @endforeach
            </div>

            {{-- Container Flex para alinhar Contador à esquerda e Ordenação à direita --}}
            <div class="flex items-center justify-between mb-lg mt text-muted text-sm">
                {{-- Contador (Lado esquerdo) --}}
                <div id="results-count">
                    {{ $linhas->count() }} {{ $linhas->count() === 1 ? 'resultado encontrado' : 'resultados encontrados' }}
                </div>

                {{-- Filtro de Ordenação (Lado direito - Adicionado conforme image_2.png) --}}
                <div>
                    <select id="sort-selector" class="field-input">
                        <option value="api" selected>Sem ordenação</option>
                        <option value="preco">Menor preço</option>
                        <option value="duracao">Menor duração</option>
                    </select>
                </div>
            </div>

            {{-- Lista de Cards --}}
            <div id="cards-container" class="lista-resultados">
                @forelse ($linhas as $linha)
                    {{-- Mantida a estrutura HTML pura com o atributo data-categoria necessário para o script funcionar --}}
                    <x-linha-card
                        :linha="$linha"
                        :data-categoria="$linha->categoria?->value"
                        :data-preco-min="$linha->precoMinimo"
                        :data-duracao-min="$linha->duracaoMinutos"
                    />
                @empty
                    <x-empty-state
                        title="Nenhuma viagem encontrada para esta data."
                        message="Considere mudar a data ou origem/destino e tente novamente."
                        icon="{{asset('favicon.ico')}}"
                    />
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
            const container = document.getElementById('cards-container');
            const countElement = document.getElementById('results-count');
            const sortSelector = document.getElementById('sort-selector');
            const todosFiltros = document.querySelectorAll('[data-filter]');

            const originalOrder = Array.from(container?.children || []);
            const totalItems = originalOrder.length;

            function updateCounter() {
                if (!countElement || !container) return;
                const botaoAtivo = document.querySelector('[data-filter][aria-pressed="true"]');
                const filtroAtivo = botaoAtivo ? botaoAtivo.dataset.filter : 'todas';
                const visiveis = Array.from(container.children).filter(card => card.style.display !== 'none').length;

                if (filtroAtivo === 'todas') {
                    countElement.textContent = totalItems === 1
                        ? '1 resultado encontrado'
                        : `${totalItems} resultados encontrados`;
                } else {
                    countElement.textContent = `${visiveis} de ${totalItems} resultados`;
                }
            }

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
                Array.from(container.children).forEach(card => {
                    card.style.display = (filtroAtivo === 'todas' || card.dataset.categoria === filtroAtivo) ? '' : 'none';
                });

                updateCounter();
            });

            sortSelector?.addEventListener('change', function () {
                if (!container) return;
                const sortBy = this.value;

                if (sortBy === 'api') {
                    originalOrder.forEach(node => container.appendChild(node));
                    updateCounter();
                    return;
                }

                Array.from(container.children)
                    .sort((a, b) => {
                        if (sortBy === 'preco') {
                            return Number(a.dataset.precoMin || 0) - Number(b.dataset.precoMin || 0);
                        }
                        if (sortBy === 'duracao') {
                            return Number(a.dataset.duracaoMin || 0) - Number(b.dataset.duracaoMin || 0);
                        }
                        return 0;
                    })
                    .forEach(node => container.appendChild(node));

                updateCounter();
            });
        </script>
    @endpush
@endsection
