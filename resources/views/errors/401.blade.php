@extends('errors::minimal')

@section('code', '401')
@section('title', 'Não autorizado')
@section('message', 'Você precisa estar logado para acessar esta página.')

@section('extra-content')
    <a href="{{ route('login') }}" class="btn btn--primary">
        Fazer login
    </a>
    <a href="{{ route('home') }}" class="btn btn--outline">
        Voltar ao início
    </a>
@endsection
