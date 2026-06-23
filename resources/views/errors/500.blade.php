@extends('layouts.app')

@section('code', '500')
@section('title', 'Erro interno no servidor ')
@section('message', 'Algo deu errado no nosso lado. Já fomos avisados e estamos resolvendo.')

@section('error-content')
    <a href="{{ route('home') }}" class="btn btn-primary">
        Voltar ao início
    </a>

    @if(!empty($traceId))
        <p class="error-list">
            Informe este código ao suporte: <strong>{{ $traceId }}</strong>
        </p>
    @endif
@endsection
