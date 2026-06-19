@props([
    'title' => 'Nada encontrado',
    'message' => 'Tente novamente',
    'icon' => null,
])

<div class="empty-state">
    @if(isset($icon))
        <div class="empty-state_icon">
            <img src="{{ $icon }}" alt="Ícone de Empty state">
        </div>
    @endif

    <h2 class="empty-state_title">
        {{ $title }}
    </h2>

    <p class="empty-state_message">
        {{ $message }}
    </p>

    <div>
        {{ $slot }}
    </div>
</div>
