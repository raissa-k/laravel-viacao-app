{{-- Mensagem flash de sessão (success / danger).
     Incluída em ambos os layouts com @include('partials.flash').
     session()->has() verifica sem consumir; session() lê e consome a mensagem. --}}
@php
    $flashType    = session()->has('success') ? 'success' : (session()->has('danger') ? 'danger' : null);
    $flashMessage = $flashType ? session($flashType) : null;
@endphp

@if ($flashType)
    <div class="flash">
        <div class="flash__box flash__box--{{ $flashType }}">
            <strong>{{ strtoupper($flashType) }}:</strong>
            {{ $flashMessage }}
        </div>
    </div>
@endif
