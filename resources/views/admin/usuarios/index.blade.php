{{-- View admin de usuários: listagem somente leitura.
Compare com src/views/admin/usuarios/index.php do PHP puro.
Estrutura idêntica: filtro de busca + tabela. --}}
@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

<p class="muted small">
    Usuários são cadastrados via seed/banco diretamente. CRUD de usuários será implementado em breve.
</p>

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
        <span class="filter-label">&nbsp;</span>
        <div class="actions">
            <button type="submit">Filtrar</button>
            <a href="{{ route('usuarios.index') }}">Limpar</a>
        </div>
    </div>
</form>

@if ($usuarios->isEmpty())
    <p>Nenhum usuário cadastrado.</p>
@else
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Cadastrado em</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($usuarios as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->nome }}</td>
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
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection
