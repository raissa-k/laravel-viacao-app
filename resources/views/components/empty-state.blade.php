@props([
    'title' => 'Nada encontrado',
    'message' => 'Tente novamente',
    'icon' => null,
    //$slot para o link
])

<div class="empty-state">
    @if($icon)
        <div class="empty-state__icon">
            {{ $icon }}
        </div>
    @endif

    <h2 class="empty-state__title">
        {{ $title }}
    </h2>

    <p class="empty-state__message">
        {{ $message }}
    </p>

    {{ $slot }}
</div>
