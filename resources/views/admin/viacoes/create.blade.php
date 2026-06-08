{{-- View de cadastro de viação.
Compare com src/views/admin/viacoes/create.php do PHP puro.
$errors->any() e $errors->all() vêm do FormRequest: o Laravel injeta automaticamente.
old('campo') repopula o campo após erro de validação, igual ao $old['campo'] do PHP puro. --}}
@extends('layouts.app')

@section('title', $title)

@section('content')

<h1>{{ $title }}</h1>

{{-- Erros de validação: mostrar antes do form melhora acessibilidade (leitores de tela leem de cima pra baixo). --}}
@if ($errors->any())
    <div class="alert alert--danger">
        <ul class="error-list">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- enctype="multipart/form-data" é OBRIGATÓRIO pra enviar arquivos. Sem ele, o PHP recebe $_FILES vazio e o upload silencia sem erro. --}}
<form method="POST" action="{{ route('viacoes.store') }}" enctype="multipart/form-data">
    {{-- @@csrf gera o token oculto: equivalente ao View::csrfField() do PHP puro. O Laravel valida esse token automaticamente em POST/PUT/DELETE. --}}
    @csrf

    <div class="form-group">
        <label for="nome">Nome</label>
        <input
            type="text"
            id="nome"
            name="nome"
            value="{{ old('nome') }}"
            required
            maxlength="255"
        >
    </div>

    <div class="form-group">
        <label for="cidade">Cidade</label>
        <select name="cidade" id="cidade" class="field-input" required>
            <option value="">Selecione uma cidade...</option>
            @foreach($cidades as $cidade)
                <option value="{{ $cidade->nome }}" {{ old('cidade') === $cidade->nome ? 'selected' : '' }}>
                    {{ $cidade->nome }} {{ $cidade->uf ? '- ' . $cidade->uf : '' }}
                </option>
            @endforeach
        </select>
        @error('cidade')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        {{-- Checkbox: quando desmarcado não aparece no POST.
        O ViacaoRequest usa $this->boolean('ativa') que retorna false quando ausente.
        Mesmo comportamento do PHP puro (isset($input['ativa'])). --}}
        <label>
            <input
                type="checkbox"
                name="ativa"
                value="1"
                @checked(old('ativa', true))
            >
            Viação ativa (aparece na home pública)
        </label>
    </div>

    <div class="form-group">
        <label for="logo">Logo (opcional - JPG, PNG ou WEBP, máx. 2&nbsp;MB)</label>
        {{-- A validação de MIME e tamanho fica no ViacaoRequest. No PHP puro, era feita no UploadService. --}}
        <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp">
        @error('logo')
            <span class="error-list" style="padding-left: 0">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-actions">
        <button type="submit">Salvar viação</button>
        <a href="{{ route('viacoes.index') }}">Cancelar</a>
    </div>

</form>

@endsection
