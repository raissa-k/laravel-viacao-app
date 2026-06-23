@extends('layouts.public')

@section('title', 'Erro Interno no Servidor')

@section('content')
    <section class="section-alt">
        <div class="container">
            <div class="error-list">

                <p class="big-button-error-blue">500</p>

                <h1 class="card-title hero-title">Erro Interno no Servidor</h1>

                <p class="button-error">Algo deu errado no nosso lado. Já fomos avisados e estamos resolvendo.</p>

                <a href="{{ route('home') }}" class="btn btn-primary">
                    Voltar ao início
                </a>

                @if(!empty($traceId))
                    <p class="error-list-sos">
                        Informe este código ao suporte: <strong>{{ $traceId }}</strong>
                    </p>
                @endif

            </div>
        </div>
    </section>
@endsection
