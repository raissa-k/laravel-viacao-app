{{-- View de edição de viação.
Compare com src/views/admin/viacoes/edit.php do PHP puro.
@method('PUT') gera o campo _method oculto que o Laravel usa pra rotear pra update().
old('campo', $viacao->campo): repopula com o valor atual se não houve erro, ou com o digitado se houve. --}}
@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

<p>
    <a href="{{ route('viacoes.show', $viacao) }}">Ver histórico desta viação</a>
</p>

@if ($errors->any())
    <div class="alert alert--danger">
        <ul class="error-list">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('viacoes.update', $viacao) }}" enctype="multipart/form-data">
    @csrf
    {{-- @@method('PUT'): browsers só enviam GET e POST via <form>.
    O Laravel detecta o campo _method e reescreve o verbo pra PUT antes de despachar.
    Mesma estratégia do method spoofing do PHP puro (View::methodField('PUT')). --}}
    @method('PUT')

    <div class="form-group">
        <label for="nome">Nome</label>
        <input
            type="text"
            id="nome"
            name="nome"
            value="{{ old('nome', $viacao->nome) }}"
            required
            maxlength="255"
        >
    </div>

    <div class="form-group">
        <label for="cidade">Cidade</label>
        <input
            type="text"
            id="cidade"
            name="cidade"
            value="{{ old('cidade', $viacao->cidade) }}"
            required
            maxlength="255"
        >
    </div>

    <div class="form-group">
        <label for="site">Site</label>
        <input
            type="text"
            id="site"
            name="site"
            value="{{ old('site', $viacao->site) }}"
            maxlength="255"
        >
    </div>
    <div class="form-group">
        <label>
            <input
                type="checkbox"
                name="ativa"
                value="1"
                @checked(old('ativa', $viacao->ativa))
            >
            Viação ativa (aparece na home pública)
        </label>
    </div>

    <div class="form-group">
        <label for="logo">Logo (JPG, PNG ou WEBP, máx. 2&nbsp;MB)</label>

        @if ($viacao->logo !== null)
            <p class="small muted">
                Logo atual:
                <img
                    class="logo-preview"
                    src="{{ route('uploads.serve', $viacao->logo) }}"
                    alt="Logo atual da {{ $viacao->nome }}"
                >
            </p>
            <p class="small muted">Envie um novo arquivo pra substituir o logo atual.</p>
        @endif

        <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp">
        @error('logo')
            <span class="error-list" style="padding-left: 0">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-actions">
        <button type="submit">Salvar alterações</button>
        <a href="{{ route('viacoes.index') }}">Cancelar</a>
    </div>

</form>

@endsection
