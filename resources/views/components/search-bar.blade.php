@props(['layout' => 'vertical'])

<div class="card search-card-{{ $layout }}">
    @if($layout === 'vertical')
        <h2 class="card-title">Buscar passagem</h2>
    @endif

    <form class="search-form {{ $layout === 'horizontal' ? 'search-form-horizontal' : '' }}" action="{{ route('busca') }}" method="GET">

        <div class="field">
            <label class="field-label" for="origem">Origem</label>
            <input
                class="field-input"
                type="text"
                id="origem"
                name="origem"
                placeholder="De onde você vai sair?"
                value="{{ request('origem') }}"
            >
        </div>

        <div class="field">
            <label class="field-label" for="destino">Destino</label>
            <input
                class="field-input"
                type="text"
                id="destino"
                name="destino"
                placeholder="Para onde você vai?"
                value="{{ request('destino') }}"
            >
        </div>

        <div class="field-row">
            <div class="field">
                <label class="field-label" for="data">Data</label>
                <input
                    class="field-input"
                    type="date"
                    id="data"
                    name="data"
                    value="{{ request('data') }}"
                >
            </div>
            <div class="field">
                <label class="field-label" for="passageiros">Passageiros</label>
                <select
                    class="field-input"
                    id="passageiros"
                    name="passageiros"
                >
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
