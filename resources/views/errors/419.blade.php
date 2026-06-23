@extends('errors::minimal')

@section('code', '419')
@section('title', 'Sessão expirada')
@section('message', 'Sua sessão expirou por inatividade. Por favor, volte e tente novamente.')

@section('extra-content')
    <button onclick="history.back()" class="btn btn-outline">
        Voltar e tentar novamente
    </button>
    <a href="{{ route('home') }}" class="btn btn-outline">
        Ir para o início
    </a>
@endsection
