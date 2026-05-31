{{--
    Template de e-mail para TODAS as notificações enviadas via canal "mail".

    COMO FOI PUBLICADO:
        php artisan vendor:publish --tag=laravel-notifications

    POR QUE PUBLICAR?
    O Laravel usa este arquivo internamente para renderizar e-mails de notificação.
    Sem publicar, ele fica trancado dentro do vendor/ e você não pode editar.
    Após publicar, este arquivo em resources/views/vendor/ tem prioridade sobre o original.

    COMO FUNCIONA O FLUXO:
    1. Você cria uma classe em app/Notifications/MeuAviso.php
    2. Declara via(): return ['mail']
    3. Implementa toMail(): retorna new MailMessage com greeting, lines, action etc.
    4. O Laravel renderiza este template com as variáveis do MailMessage

    VARIÁVEIS DISPONÍVEIS NESTE TEMPLATE:
    - $greeting  : string opcional - saudação personalizada (ex: "Olá, João!")
    - $level     : 'success' | 'error' | 'info' - afeta a cor do botão de ação
    - $introLines: array de strings - parágrafos de introdução antes do botão
    - $actionText: string opcional - texto do botão de ação
    - $actionUrl : string opcional - URL do botão de ação
    - $outroLines: array de strings - parágrafos após o botão
    - $salutation: string opcional - despedida personalizada (ex: "Até logo,")
    - $displayableActionUrl: versão legível da URL para o subcopy em texto simples

    COMPONENTES DISPONÍVEIS (x-mail::*):
    - <x-mail::message>          : wrapper principal, aplica o layout HTML/CSS
    - <x-mail::button :url color>: botão de call-to-action
    - <x-mail::panel>            : bloco destacado com borda colorida
    - <x-mail::table>            : tabela formatada para e-mail

    CONTEÚDO SUPORTA MARKDOWN:
    O conteúdo desta view é processado pelo Markdown do Laravel.
    Você pode usar **negrito**, _itálico_, [links](url), etc.

    PARA CUSTOMIZAR O LAYOUT VISUAL (cores, logo, fonte):
    Edite resources/views/vendor/mail/html/
    - themes/default.css : cores, espaçamento, tipografia
    - header.blade.php   : logo e nome do app no topo do e-mail
    - footer.blade.php   : rodapé com copyright
    - button.blade.php   : estilo do botão CTA

    Pesquise: "Laravel Mail Notifications", "MailMessage", "toMail()", "markdown mailables"
--}}
<x-mail::message>
{{-- Saudação: personalizada via ->greeting() ou padrão por nível --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Linhas de introdução: adicionadas com ->line() antes do botão --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Botão de ação: aparece apenas se ->action('Texto', 'url') foi chamado --}}
@isset($actionText)
<?php
    // A cor do botão reflete o nível da notificação:
    // 'success' → verde, 'error' → vermelho, outros → preto (primary)
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Linhas finais: adicionadas com ->line() após o botão --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Despedida: personalizada via ->salutation() ou padrão com nome do app --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards,')<br>
{{ config('app.name') }}
@endif

{{-- Subcopy: fallback para clientes que bloqueiam botões HTML --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
