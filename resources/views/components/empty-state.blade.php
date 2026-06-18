@props([
    'title' => 'Nada encontrado',
    'message' => 'Tente novamente',
    'icon' => null,
])

<div class="empty-state">
    <div class="empty-state_icon">
        <img src="{{ asset('favicon.ico') }}" alt="Ícone de Empty state">
    </div>

    <h2 class="empty-state_title">
        {{ $title }}
    </h2>

    <p class="empty-state_message">
        {{ $message }}
    </p>

        {{ $slot }}

</div>
