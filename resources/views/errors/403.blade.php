@extends('errors::minimal')

@section('code', '403')
@section('title', 'Acesso negado')
@section('message', $exception->getMessage() ?: 'Você não tem permissão para acessar esta página.')

@section('extra-content')
    <a href="{{ route('home') }}" class="btn btn--primary">
        Voltar ao início
    </a>
@endsection
