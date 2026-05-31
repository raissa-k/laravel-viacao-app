{{-- View admin do histórico: filtros + tabela de alterações.
$h é um ViacaoHistorico (Eloquent model).
O cast json:unicode decodifica alteracoes para array automaticamente.
Os relacionamentos viacao/usuario são eager-loaded em HistoricoService (with()),
então $h->viacao->nome não dispara queries adicionais aqui. --}}
@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

{{-- Form de filtro usa GET (não POST):
1. URL vira compartilhável
2. Botão "voltar" do browser funciona corretamente
POST é pra MUDAR dados, GET é pra LER/FILTRAR. Pesquise "HTTP idempotency". --}}
<form class="filter-form" method="GET" action="{{ route('historico.index') }}">

    {{-- viacao_id preservado como campo oculto: o link "Ver histórico desta viação"
    na tela de edição passa esse parâmetro, e ele precisa sobreviver ao submit do form. --}}
    @if ($filter->viacaoId !== null)
        <input type="hidden" name="viacao_id" value="{{ $filter->viacaoId }}">
    @endif

    <div class="filter-field">
        <label class="filter-label" for="f-q">Busca</label>
        <input
            class="filter-input-lg"
            id="f-q"
            type="text"
            name="q"
            placeholder="Nome de viação ou usuário"
            value="{{ $filter->q }}"
        >
    </div>

    <div class="filter-field">
        <label class="filter-label" for="f-acao">Ação</label>
        <select class="filter-input-md" id="f-acao" name="acao">
            <option value="">Todas</option>
            {{-- AcaoHistorico::cases() devolve todos os cases do enum em ordem de declaração.
            Se um novo valor for adicionado ao enum, ele aparece aqui automaticamente.
            Antes: array hardcoded ['Criado', 'Editado', 'Excluido'], fácil de esquecer de atualizar.
            Agora: enum é a fonte de verdade, a view só itera.
            $caso->value extrai a string 'Criado'/'Editado'/'Excluido'.
            $filter->acao === $caso compara enum com enum (não string com string) --}}
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
            <a href="{{ route('historico.index') }}">Limpar</a>
        </div>
    </div>

</form>

@if ($historico->isEmpty())
    <p class="muted">Nenhum registro encontrado.</p>
@else

    <p class="small muted">{{ $historico->count() }} registro(s) encontrado(s)</p>

    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Viação</th>
                <th>Usuário</th>
                <th>Ação</th>
                <th>Alterações</th>
                <th>Quando</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($historico as $h)
            <tr>
                <td class="small muted">{{ $h->id }}</td>
                <td>{{ $h->viacao->nome ?: '---' }}</td>
                <td>{{ $h->usuario->nome ?: '---' }}</td>
                <td>{{ $h->acao }}</td>
                <td>
                    @if (is_array($h->alteracoes))
                        {{-- <details> e <summary>: elementos HTML nativos pra expandir/recolher,
                        sem JavaScript. Pesquise "HTML details element" no MDN. --}}
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
                <td class="small muted">
                    {{-- $h->criado_em é uma instância de Carbon (Laravel Eloquent automático).
                    Podemos usar métodos Carbon aqui:
                    - ->format('Y-m-d H:i'): formatação clássica
                    - ->translatedFormat('d \\d\\e F \\d\\e Y'): respeita locale (pt_BR)
                    - ->diffForHumans(): "1 hour ago", "5 minutes ago" (locale-aware)
                    - ->toFormattedDateString(): "Jan 2, 2026"

                    Carbon é perfeito pra exibir datas em views porque oferece coisa pronta e respeita a configuração de locale do Laravel.

                    Comparação:
                    - DateTime nativo: $d->format('Y-m-d H:i')
                    - Carbon: $d->format('Y-m-d H:i') (OU $d->translatedFormat('d \\d\\e F'))

                    Pesquise "Carbon formatting", "Carbon localization", "Carbon diffForHumans".
                    --}}
                    {{ $h->criado_em->format('d/m/Y H:i') }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endif

@endsection
