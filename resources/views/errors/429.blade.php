@extends('errors::minimal')

@section('code', '429')
@section('title', 'Muitas tentativas')
@section('message', 'Você fez muitas requisições em pouco tempo. Aguarde alguns instantes e tente novamente.')

@section('extra-content')
    <a href="{{ route('home') }}" class="btn btn-primary">
        Voltar ao início
    </a>
@endsection
