{{-- Listagem admin de usuários: CRUD completo, filtros, paginação e ações condicionais. --}}
@use('Illuminate\Support\Js')

@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

<p><a href="{{ route('usuarios.create') }}">Cadastrar novo usuário</a></p>

<form class="filter-form" method="GET" action="{{ route('usuarios.index') }}">
    <div class="filter-field">
        <label class="filter-label" for="f-q">Busca</label>
        <input
            class="filter-input-lg"
            id="f-q"
            type="text"
            name="q"
            placeholder="Nome ou e-mail"
            value="{{ $filter->q }}"
        >
    </div>

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
            <a href="{{ route('usuarios.index') }}">Limpar</a>
        </div>
    </div>
</form>

@if ($usuarios->isEmpty())
    <x-empty-state
        title="Nenhum usuário encontrado"
        message="Tente limpar seus filtros ou criar um novo usuário."
        icon="{{asset('favicon.ico')}}"
    />
@else
    <p class="small muted">{{ $usuarios->total() }} usuário(s) encontrado(s)</p>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Cadastrado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $u)
                <tr>
                    <td class="small muted">{{ $u->id }}</td>
                    <td>
                        @if (!$u->trashed())
                            <a href="{{ route('usuarios.show', $u) }}">{{ $u->nome }}</a>
                        @else
                            <span class="muted">{{ $u->nome }}</span>
                        @endif
                    </td>
                    <td>{{ $u->email }}</td>
                    {{-- Carbon no Laravel: $u->created_at é automaticamente uma instância Carbon.
                    Aqui formatamos com ->format() pra exibir data legível.

                    Métodos úteis do Carbon:
                    - ->format('d/m/Y H:i'): formatação clássica
                    - ->toDateString(): "2026-01-23"
                    - ->toDayDateTimeString(): "Friday, January 23, 2026 3:45 PM"
                    - ->diffForHumans(): "2 hours ago" (com locale)
                    - ->isToday(), ->isYesterday(), ->isBetween(): comparações
                    - ->addDays(7), ->subMonths(1): manipulação

                    Pesquise: "Carbon methods", "Carbon chaining", "Carbon timezone handling".
                    --}}
                    <td class="muted small">{{ $u->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="actions">
                            @if (!$u->trashed())
                                <a href="{{ route('usuarios.show', $u) }}">Ver</a>
                                <a href="{{ route('usuarios.edit', $u) }}">Editar</a>
                                {{-- Não exibe "Excluir" para o usuário logado --}}
                                @if ($u->id !== auth()->id())
                                    <form
                                        class="inline-form"
                                        method="POST"
                                        action="{{ route('usuarios.destroy', $u) }}"
                                        onsubmit="return confirm('Confirmar exclusão de ' + {{ Js::from($u->nome) }} + '?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Excluir</button>
                                    </form>
                                @endif
                            @else
                                <form
                                    class="inline-form"
                                    method="POST"
                                    action="{{ route('usuarios.restore', $u->id) }}"
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

    <div class="paginacao">
        {{ $usuarios->links() }}
    </div>
@endif

@endsection
