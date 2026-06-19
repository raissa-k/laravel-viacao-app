{{-- Histórico unificado: abas para viações e usuários.
A aba ativa é controlada pelo parâmetro GET 'entidade' (via HistoricoFilterDTO -> EntidadeHistorico enum).
Troca de aba = nova requisição GET preservando os outros filtros.
Pesquise "URL-based tabs", "HTTP idempotency", "GET vs POST". --}}

{{-- @use importa o enum pra uso direto na view, sem FQCN nos comparativos.
Mesmo padrão do @use('Illuminate\Support\Js') em outras views. --}}

@extends('layouts.app')

@section('title', $title)
@use('App\Enums\EntidadeHistorico')
@section('content')

<h1>{{ $title }}</h1>

{{-- ABAS: links GET com ?entidade=viacao ou ?entidade=usuario.
O href usa ->value pra gerar a string do parâmetro de URL. --}}
<div class="abas">
    <a
        class="aba {{ $filter->entidade === EntidadeHistorico::Viacao ? 'aba-ativa' : '' }}"
        href="{{ route('historico.index', array_merge(request()->except(['entidade', 'page']), ['entidade' => EntidadeHistorico::Viacao->value])) }}"
    >
        Viações
    </a>
    <a
        class="aba {{ $filter->entidade === EntidadeHistorico::Usuario ? 'aba-ativa' : '' }}"
        href="{{ route('historico.index', array_merge(request()->except(['entidade', 'page']), ['entidade' => EntidadeHistorico::Usuario->value])) }}"
    >
        Usuários
    </a>
</div>

<form class="filter-form" method="GET" action="{{ route('historico.index') }}">
    <input type="hidden" name="entidade" value="{{ $filter->entidade->value }}">

    <div class="filter-field">
        <label class="filter-label" for="f-q">Busca</label>
        <input
            class="filter-input-lg"
            id="f-q"
            type="text"
            name="q"
            placeholder="{{ $filter->entidade === EntidadeHistorico::Usuario ? 'Nome de usuário' : 'Nome de viação' }}"
            value="{{ $filter->q }}"
        >
    </div>

    <div class="filter-field">
        <label class="filter-label" for="f-acao">Ação</label>
        <select class="filter-input-md" id="f-acao" name="acao">
            <option value="">Todas</option>
            @foreach (\App\Enums\AcaoHistorico::cases() as $caso)
                <option value="{{ $caso->value }}" @selected($filter->acao === $caso)>
                    {{ $caso->value }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-field">
        <label class="filter-label" for="f-de">De</label>
        <input
            id="f-de"
            type="date"
            name="date_from"
            value="{{ $filter->dateFrom ?? '' }}"
        >
    </div>

    <div class="filter-field">
        <label class="filter-label" for="f-ate">Até</label>
        <input
            id="f-ate"
            type="date"
            name="date_to"
            value="{{ $filter->dateTo ?? '' }}"
        >
    </div>

    <div class="filter-field">
        <span class="filter-label">&nbsp;</span>
        <div class="actions">
            <button type="submit">Filtrar</button>
            <a href="{{ route('historico.index', ['entidade' => $filter->entidade]) }}">Limpar</a>
        </div>
    </div>
</form>

@if ($historico->isEmpty())
    <x-empty-state
        title="Histórico vazio"
        message="Tente modificar alguma viação ou usuário ou limpe seus filtros."
        icon="{{asset('favicon.ico')}}"
    />
@else

    <p class="small muted">{{ $historico->total() }} registro(s) encontrado(s)</p>

    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ $filter->entidade === EntidadeHistorico::Usuario ? 'Usuário' : 'Viação' }}</th>
                <th>Ator</th>
                <th>Ação</th>
                <th>Alterações</th>
                <th>Quando</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($historico as $h)
            <tr>
                <td class="small muted">{{ $h->id }}</td>
                <td>{{ $h->entidade->nome ?: '---' }}</td>
                <td>
                    {{ $h->ator->nome ?: '---' }}
                    @if ($h->ator?->trashed())
                        <div class="small muted">usuário excluído</div>
                    @endif
                </td>
                <td>
                    <x-badge
                        rotulo="{{ $h->acao->value }}"
                        tipo="{{ $h->acao->tipoBadge() }}"
                    />
                </td>
                <td>
                    @if (is_array($h->alteracoes))
                        <details>
                            <summary class="small">Ver alterações</summary>
                            <pre class="diff-pre">Antes:
{{ json_encode($h->alteracoes['before'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}

Depois:
{{ json_encode($h->alteracoes['after'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    @else
                        <span class="muted small">---</span>
                    @endif
                </td>
                <td class="small muted">{{ $h->criado_em->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="paginacao">
        {{ $historico->links() }}
    </div>

@endif

@endsection

