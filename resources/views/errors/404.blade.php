@extends('layouts.public')

@section('title', 'Página Não Encontrada')

@section('content')
    <section class="section-alt">
        <div class="container">
            <div class="error-list">


                <p class="big-button-error-blue">404</p>

                <h1 class="card-title hero-title">Página Não Encontrada</h1>

                <p class="button-error">A página que você está procurando foi movida ou não existe.</p>

                <a href="{{ route('home') }}" class="btn btn-primary">
                    Voltar para o Início
                </a>

                <div class="form-group">
                    <h2 class="card-title">Procure sua próxima viagem aqui</h2>
                    <x-search-bar
                        :cidades="$cidades"
                        :action="route('busca')"
                        layout="horizontal"
                    />
                </div>

            </div>
        </div>
    </section>
@endsection
