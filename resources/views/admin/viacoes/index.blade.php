{{-- View de listagem admin das viações.
Compare com src/views/admin/viacoes/index.php do PHP puro.
{{ }} escapa automaticamente (htmlspecialchars). @if/@foreach substituem <?php if/foreach ?>.
@csrf e @method() substituem os helpers manuais.

IMPORTANTE: route() vs hardcoded URLs
- No PHP puro: href="/admin/viacoes" (string hardcoded então se muda a rota, quebra)
- No Laravel: href="{{ route('viacoes.index') }}" (URL nomeada então se muda a rota, aqui atualiza automático)
- Pesquise "named routes Laravel", "benefits of route() helper"

@selected() e @checked() são "syntactic sugar" do Blade:
- {{ $filter->ativa === true ? 'selected' : '' }} é long, ilegível
- @selected($filter->ativa === true) gera o atributo apenas se true
- Mesmo para checkbox: @checked(old('ativa', true))
- Pesquise "Blade directives", "conditional attributes"
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
                <td>{{ $v->nome }}</td>
                <td>{{ $v->cidade }}</td>
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
                        <a href="{{ route('viacoes.edit', $v) }}">Editar</a>

                        {{-- Form de exclusão: @@method('DELETE') gera o campo _method oculto.
                        O Laravel detecta _method e reescreve o verbo, igual ao PHP puro.
                        @@csrf gera o token oculto, equivalente ao View::csrfField(). --}}
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
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@endsection
