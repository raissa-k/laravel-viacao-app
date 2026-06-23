@props([
    'layout' => 'vertical',
    'cidades' => null,
    'action' => null,
]);
@extends('layouts.public')

@section('title', 'Página não encontrada')
@section('code', '404')
@section('message', 'A página que você está procurando foi movida ou não existe.')

@section('content')
    @php
        $cidades = $cidades ?? \App\Models\Cidade::orderBy('nome')->get();
    @endphp
    <a href="{{ route('home') }}" class="btn btn-primary">
        Voltar para Início
    </a>

        <h2 class="card-title">Procure sua próxima viagem aqui</h2>

        <x-search-bar
        :cidades="$cidades"
        :action="route('busca')"
        />
@endsection
