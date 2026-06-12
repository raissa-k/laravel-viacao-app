@props([
    'layout' => 'vertical',
    'cidades' => null,
    'action' => null,
    ])

@php
    $cidades = collect($cidades ?? []);
    $action =  $action ?? route('home');
@endphp


<div class="card search-card-{{ $layout }}">
    @if($layout === 'vertical')
        <h2 class="card-title">Buscar passagem</h2>
    @endif

    <form class="search-form {{ $layout === 'horizontal' ? 'search-form-horizontal' : '' }}"
          action="{{ $action }}" method="GET">

        <div class="field">
            <label class="field-label" for="origem">Origem</label>
            <select
                class="field-input"
                id="origem"
                name="origem"
            >
                <option>
                    selecione uma cidade
                </option>

                @foreach($cidades as $cidade)
                    <option value="{{ $cidade->id }}"
                    @selected(old('origem', request('origem')) == $cidade->id)>
                        {{ $cidade->nome }}{{ $cidade->uf ? ' - ' . $cidade->uf : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label class="field-label" for="destino">Destino</label>

            <select
                class="field-input"
                id="destino"
                name="destino"
            >@foreach($cidades as $cidade)
                    <option
                        value="{{ $cidade->id }}"
                        @selected(old('destino', request('destino')) == $cidade->id)
                    >
                        {{ $cidade->nome }}{{ $cidade->uf ? ' - ' . $cidade->uf : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="field-row">
            <div class="field">
                <label class="field-label" for="data">Data</label>
                <input
                    class="field-input"
                    type="date"
                    id="data"
                    name="data"
                    value="{{ old('data', request('data')) }}"
                >
            </div>
            <div class="field">
                <label class="field-label" for="passageiros">Passageiros</label>
                <select class="field-input" id="passageiros" name="passageiros">
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected(request('passageiros', '1') == $i)>
                            {{ $i }} {{ $i === 1 ? 'passageiro' : 'passageiros' }}
                        </option>
                    @endfor
                </select>
            </div>
        </div>

        <button class="btn btn-blue" type="submit">Buscar passagem</button>
    </form>
</div>
