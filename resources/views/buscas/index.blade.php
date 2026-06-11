@extends('layouts.public')

@section('title', 'Passagens de ' . request('origem') . ' para ' . request('destino'))

@section('content')
    <section class="section section-alt">
        <div class="container">

            {{-- Formulário repopulado nativamente devido ao request() no componente --}}
            <div class="mb-lg">
                <x-search-bar layout="horizontal" />
            </div>

            {{-- Seção de diferenciais adicionada conforme o code review --}}
            <x-diferenciais />

            <div class="flex items-center justify-between mb-md mt-lg">
                <div>
                    <h1 class="text-xl font-bold">Resultados da Busca</h1>
                    <p class="text-muted">Mostrando viagens de <strong>{{ request('origem') }}</strong> para <strong>{{ request('destino') }}</strong> no dia {{ date('d/m/Y', strtotime(request('data'))) }}</p>
                </div>
            </div>

            {{-- Filtros de Categoria (Client-Side) --}}
            {{-- Quando o enum categoria estiver disponível, esses filtros deverão ser criados a partir dos cases do Enum. --}}
            <div class="filtros-categoria flex gap-sm mb-lg">
                <button class="filtro-pill" aria-pressed="false" data-filter="todas">Todas</button>
                <button class="filtro-pill" aria-pressed="false" data-filter="convencional">Convencional</button>
                <button class="filtro-pill" aria-pressed="false" data-filter="executivo">Executivo</button>
                <button class="filtro-pill" aria-pressed="false" data-filter="leito">Leito</button>
            </div>

            {{-- Lista de Cards --}}
            <div class="grid-auto lista-resultados">
                @forelse ($linhas as $linha)
                    {{-- Componente x-linha-card removido por não ter sido entregue ainda. --}}
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

    {{-- Script de filtro do layout public --}}
    @push('scripts')
        <script>
            // Usando delegação de eventos para não depender de classes CSS
            document.addEventListener('click', (event) => {
                // Identifica o botão estritamente pelo atributo
                const botaoClicado = event.target.closest('[data-filter]');

                if (!botaoClicado) return;

                const todosFiltros = document.querySelectorAll('[data-filter]');
                const todosCards = document.querySelectorAll('[data-categoria]');
                const filtroAtivo = botaoClicado.getAttribute('data-filter');

                // 1. Volta TODOS os botões para false (Isso já cumpre a regra do botão "Todas")
                todosFiltros.forEach(btn => btn.setAttribute('aria-pressed', 'false'));

                // 2. REGRA CHAVE: Só marca como "true" se o filtro clicado NÃO for o "todas"
                if (filtroAtivo !== 'todas') {
                    botaoClicado.setAttribute('aria-pressed', 'true');
                }

                // 3. Aplica o filtro nos cards identificando pelo [data-categoria]
                todosCards.forEach(card => {
                    const categoriaCard = card.getAttribute('data-categoria');

                    if (filtroAtivo === 'todas' || categoriaCard === filtroAtivo) {
                        card.style.display = ''; // Mostra o card
                    } else {
                        card.style.display = 'none'; // Esconde o card
                    }
                });
            });
        </script>
    @endpush
@endsection
