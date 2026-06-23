@extends('errors::minimal')

@section('code', '402')
@section('title', 'Pagamento necessário')
@section('message', 'O acesso a esta área requer um plano ativo. Verifique sua assinatura e tente novamente.')

@section('extra-content')
    <a href="{{ route('home') }}" class="btn btn--primary">
        Voltar ao início
    </a>
@endsection
