{{-- Visualização de uma única viação com seu histórico de alterações.
O histórico é carregado diretamente pelo controller via $viacao->historico()->with('ator').
Lazy collection: nenhuma query extra ao acessar $h->ator->nome (eager-loaded no controller). --}}
@use('Illuminate\Support\Js')

@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $viacao->nome }}</h1>

<p>
    <a href="{{ route('viacoes.index') }}">← Voltar para listagem</a>
    @if (!$viacao->trashed())
        | <a href="{{ route('viacoes.edit', $viacao) }}">Editar</a>
        | <form
            class="inline-form"
            method="POST"
            action="{{ route('viacoes.destroy', $viacao) }}"
            onsubmit="return confirm('Confirmar exclusão de ' + {{ Js::from($viacao->nome) }} + '?')"
          >
            @csrf
            @method('DELETE')
            <button type="submit">Excluir</button>
          </form>
    @endif
</p>

{{-- Detalhes da viação --}}
<table class="admin-table" style="width: auto; margin-bottom: 24px">
    <tbody>
        <tr>
            <th>ID</th>
            <td>{{ $viacao->id }}</td>
        </tr>
        <tr>
            <th>Nome</th>
            <td>{{ $viacao->nome }}</td>
        </tr>
        <tr>
            <th>Cidade</th>
            <td>{{ $viacao->cidade }}</td>
        </tr>
        <tr>
            <th>Site</th>
            <td>{{ $viacao->site }}</td>
        </tr>
        <tr>
            <th>Ativa</th>
            <td>{{ $viacao->ativa ? 'Sim' : 'Não' }}</td>
        </tr>
        <tr>
            <th>Logo</th>
            <td>
                @if ($viacao->logo)
                    <img
                        class="logo-preview"
                        src="{{ route('uploads.serve', $viacao->logo) }}"
                        alt="Logo da {{ $viacao->nome }}"
                    >
                @else
                    <span class="muted small">---</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Criada em</th>
            <td class="muted small">{{ $viacao->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @if ($viacao->trashed())
            <tr>
                <th>Excluída em</th>
                <td class="muted small">{{ $viacao->deleted_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endif
    </tbody>
</table>

<h2>Histórico de alterações</h2>

@if ($historico->isEmpty())
    <p class="muted">Nenhuma alteração registrada.</p>
@else
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Ação</th>
                <th>Por</th>
                <th>Alterações</th>
                <th>Quando</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($historico as $h)
            <tr>
                <td class="small muted">{{ $h->id }}</td>
                <td>{{ $h->acao }}</td>
                <td>{{ $h->ator->nome ?: '---' }}</td>
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
@endif

@endsection
