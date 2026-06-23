@extends('layouts.public')

@section('title', 'Página não encontrada')
@section('code', '404')
@section('message', 'A página que você está procurando foi movida ou não existe.')

@section('content')
    <a href="{{ route('home') }}" class="btn btn-primary">
        Voltar para Início
    </a>

    <div class="error-list">
        <h2 class="card-title">Procure sua próxima viagem aqui</h2>
        <x-search-bar layout="horizontal" :action="route('busca')" />
    </div>
@endsection
