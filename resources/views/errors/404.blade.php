@extends('errors.layout')

@section('code', '404')
@section('title', 'Página não encontrada')
@section('message', 'A página que você está procurando foi movida ou não existe. Mas calma, vamos te ajudar a achar sua viagem.')

@section('error-content')
    <a href="{{ route('viacoes.index') }}" class="btn btn--primary btn--lg error-page__back">
        Voltar para Início
    </a>

    <div class="error-page__search">
        <x-trip-search-form
            heading="Ou procure sua próxima viagem aqui"
        />
    </div>
@endsection
