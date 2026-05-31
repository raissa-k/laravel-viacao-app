{{--
    Componente <x-mail::message>, a montagem do e-mail completo.

    Este componente é o "esqueleto" de e-mail do Laravel Mail.
    É usado em resources/views/vendor/notifications/email.blade.php
    e também em qualquer Mailable que use o driver markdown.

    SLOTS DISPONÍVEIS:
    - $slot    : conteúdo principal (corpo do e-mail em Markdown)
    - $subcopy : texto alternativo para links/botões que clientes bloqueiam (opcional)

    COMPONENTES FILHOS (cada um é um arquivo separado em html/):
    - <x-mail::layout>  : estrutura HTML raiz (layout.blade.php)
    - <x-mail::header>  : cabeçalho com nome/logo do app (header.blade.php)
    - <x-mail::subcopy> : bloco de rodapé com fallback de URL (subcopy.blade.php)
    - <x-mail::footer>  : rodapé com copyright (footer.blade.php)

    PARA CUSTOMIZAR O VISUAL:
    Edite resources/views/vendor/mail/html/themes/default.css
    É o único arquivo CSS do e-mail. As classes usadas aqui (.content-cell, etc.) são definidas nesse arquivo e injetadas inline pelo Laravel no envio.

    Pesquise: "Laravel Mail components", "x-mail slots", "mail markdown components"
--}}
<x-mail::layout>
{{-- Cabeçalho: exibe o nome do app como link para APP_URL --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Corpo principal: conteúdo em Markdown renderizado pelo notifications/email.blade.php --}}
{!! $slot !!}

{{-- Subcopy: só renderizado se $actionText estiver definido (ver email.blade.php) --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Rodapé: copyright automático com o ano atual e nome do app --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
