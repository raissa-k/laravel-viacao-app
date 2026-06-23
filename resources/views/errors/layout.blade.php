@extends('layouts::public')

@section('content')
    <section class="section-alt">
        <div class="container">
            <div class="error-list">

                @hasSection('code')
                    <p class="button-error-blue">@yield('code')</p>
                @endif

                <h1 class="card-title hero-title">@yield('title')</h1>

                @hasSection('message')
                    <p class="button-error">@yield('message')</p>
                @endif

                @yield('error-content')

            </div>
        </div>
    </section>
@endsection
