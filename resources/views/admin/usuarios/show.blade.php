{{-- Visualização de um único usuário com seu histórico de alterações.
Padrão idêntico ao viacoes/show.blade.php: detalhes + tabela de historico. --}}
@use('Illuminate\Support\Js')

@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $usuario->nome }}</h1>

<p>
    <a href="{{ route('usuarios.index') }}">← Voltar para listagem</a>
    @if (!$usuario->trashed())
        | <a href="{{ route('usuarios.edit', $usuario) }}">Editar</a>
        @if ($usuario->id !== auth()->id())
            | <form
                class="inline-form"
                method="POST"
                action="{{ route('usuarios.destroy', $usuario) }}"
                onsubmit="return confirm('Confirmar exclusão de ' + {{ Js::from($usuario->nome) }} + '?')"
              >
                @csrf
                @method('DELETE')
                <button type="submit">Excluir</button>
              </form>
        @endif
    @endif
</p>

<table class="admin-table" style="width: auto; margin-bottom: 24px">
    <tbody>
        <tr>
            <th>ID</th>
            <td>{{ $usuario->id }}</td>
        </tr>
        <tr>
            <th>Nome</th>
            <td>{{ $usuario->nome }}</td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td>{{ $usuario->email }}</td>
        </tr>
        <tr>
            <th>Cadastrado em</th>
            <td class="muted small">{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @if ($usuario->trashed())
            <tr>
                <th>Excluído em</th>
                <td class="muted small">{{ $usuario->deleted_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endif
    </tbody>
</table>

<h2>Histórico de alterações</h2>

@if ($historico->isEmpty())
    <x-empty-state
        title="🧐 Nada encontrado.."
        message="Verifique seus filtros"
        icon=""
        link=""
    />
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
