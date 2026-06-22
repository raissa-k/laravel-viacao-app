
@extends('layouts.public')

@section('content')
    <section class="section">
        <div class="container">
            <div class="error-page">
                @hasSection('code')
                    <p class="error-page__code">@yield('code')</p>
                @endif

                <h1 class="error-page__heading">@yield('title')</h1>

                @hasSection('message')
                    <p class="error-page__message">@yield('message')</p>
                @endif

                @yield('error-content')
            </div>
        </div>
    </section>
@endsection
