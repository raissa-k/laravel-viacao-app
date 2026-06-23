@extends('errors::minimal')

@section('code', '503')
@section('title', 'Site em manutenção')
@section('message', 'Estamos realizando melhorias para te atender melhor. Voltamos em breve!')

@section('extra-content')
    <a href="/" class="btn btn-primary error-list">
        Voltar ao início
    </a>
@endsection
