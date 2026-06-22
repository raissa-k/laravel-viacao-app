@extends('errors.layout')

@section('code', '500')
@section('title', 'Erro interno no servidor')
@section('message', 'Algo deu errado.')

@section('error-content')
    <a href="{{ route('viacoes.index') }}" class="btn btn--primary error-page__back">
        Voltar ao início
    </a>

    @if(!empty($traceId))
        <p class="error-page__trace-id">
            Informe este código ao suporte: <strong>{{ $traceId }}</strong>
        </p>
    @endif
@endsection
