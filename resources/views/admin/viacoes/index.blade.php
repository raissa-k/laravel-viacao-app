{{-- Listagem admin de viações com filtros, paginação e ações condicionais por status de exclusão.
NOVIDADES vs versão anterior:
- Filtro "deletado": alterna entre registros ativos e soft-deleted
- Ações condicionais: Editar/Excluir só aparecem pra ativos; Restaurar só pra excluídos
- $viacoes->links(): links de paginação gerados pelo Eloquent LengthAwarePaginator
- $viacoes->withQueryString(): garante que ?q=...&ativa=... seja preservado nos links de página
--}}

{{-- @use importa a classe para uso direto na view, equivalente ao `use` do PHP.
Js::from() serializa o valor como JSON e o marca como HTML seguro (HtmlString), o que é correto pra esse contexto JavaScript.
addslashes() (usado no PHP puro) não é seguro para todos os casos em JS. --}}
@use('Illuminate\Support\Js')

@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

<p><a href="{{ route('viacoes.create') }}">Cadastrar nova viação</a></p>

{{-- Filtros: GET pra URL compartilhável e botão "voltar" funcional --}}
<form class="filter-form" method="GET" action="{{ route('viacoes.index') }}">
    <div class="filter-field">
        <label class="filter-label" for="f-q">Busca</label>
        <input
            class="filter-input-lg"
            id="f-q"
            type="text"
            name="q"
            placeholder="Nome ou cidade"
            value="{{ $filter->q }}"
        >
    </div>

    <div class="filter-field">
        <label class="filter-label" for="f-ativa">Status</label>
        <select class="filter-input-md" id="f-ativa" name="ativa">
            <option value="">Todas</option>
            <option value="1" @selected($filter->ativa === true)>Ativas</option>
            <option value="0" @selected($filter->ativa === false)>Inativas</option>
        </select>
    </div>

    {{-- Filtro de exclusão: só aparece quando há algo útil pra mostrar.
    hidden+checkbox: o checkbox desmarcado não envia o campo; o hidden garante que 'deletado=0'
    seja enviado quando a view precisar limpar o filtro via URL.
    Aqui usamos submit automático ao mudar o select pra UX mais fluída. --}}
    <div class="filter-field">
        <label class="filter-label" for="f-deletado">Situação</label>
        <select class="filter-input-md" id="f-deletado" name="deletado" onchange="this.form.submit()">
            <option value="0" @selected(!$filter->deletado)>Não excluídos</option>
            <option value="1" @selected($filter->deletado)>Excluídos</option>
        </select>
    </div>

    <div class="filter-field">
        <span class="filter-label">&nbsp;</span>
        <div class="actions">
            <button type="submit">Filtrar</button>
            <a href="{{ route('viacoes.index') }}">Limpar</a>
        </div>
    </div>
</form>

@if ($viacoes->isEmpty())
    <p class="muted">Nenhuma viação cadastrada ainda.</p>
@else
    <p class="small muted">{{ $viacoes->total() }} viação(ões) encontrada(s)</p>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Cidade</th>
                <th>Ativa</th>
                <th>Logo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($viacoes as $v)
            <tr>
                <td class="small muted">{{ $v->id }}</td>
                <td>
                    @if (!$v->trashed())
                        <a href="{{ route('viacoes.show', $v) }}">{{ $v->nome }}</a>
                    @else
                        <span class="muted">{{ $v->nome }}</span>
                    @endif
                </td>
                <td>{{ $v->cidade?->nome ?? '---' }}</td>
                <td>{{ $v->ativa ? 'Sim' : 'Não' }}</td>
                <td>
                    @if ($v->logo !== null)
                        <img
                            class="logo-preview"
                            src="{{ route('uploads.serve', $v->logo) }}"
                            alt="Logo da {{ $v->nome }}"
                        >
                    @else
                        <span class="muted small">---</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        {{-- Ações condicionais: apenas registros não excluídos podem ser editados/excluídos.
                        deleted_at null = ativo; não null = excluído (soft deleted).
                        Pesquise "Eloquent soft deletes", "trashed()". --}}
                        @if (!$v->trashed())
                            <a href="{{ route('viacoes.show', $v) }}">Ver</a>
                            <a href="{{ route('viacoes.edit', $v) }}">Editar</a>
                            <form
                                class="inline-form"
                                method="POST"
                                action="{{ route('viacoes.destroy', $v) }}"
                                onsubmit="return confirm('Confirmar exclusão de ' + {{ Js::from($v->nome) }} + '?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit">Excluir</button>
                            </form>
                        @else
                            {{-- Restaurar só aparece pra registros excluídos. --}}
                            <form
                                class="inline-form"
                                method="POST"
                                action="{{ route('viacoes.restore', $v->id) }}"
                            >
                                @csrf
                                <button type="submit">Restaurar</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- links() gera os links de paginação.
    withQueryString() (chamado no service) preserva os filtros ativos nos links de página.
    Pesquise "Laravel pagination", "LengthAwarePaginator". --}}
    <div class="paginacao">
        {{ $viacoes->links() }}
    </div>
@endif

@endsection
