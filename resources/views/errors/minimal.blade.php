@extends('errors::layout')

@section('title', $title ?? ('Erro ' . ($code ?? '')))

@section('error-content')
    @hasSection('extra-content')
        @yield('extra-content')
    @else
        <a href="{{ route('home') }}" class="btn btn-primary error-list">
            Voltar ao início
        </a>
    @endif
@endsection
