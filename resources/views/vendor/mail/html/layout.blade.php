{{--
    Layout HTML raiz de todos os e-mails do Laravel Mail.

    COMO FOI PUBLICADO:
        php artisan vendor:publish --tag=laravel-mail

    ESTRUTURA GERAL:
    layout.blade.php  <<<< você está aqui (HTML externo, injeta CSS, recebe slots)
    └─ message.blade.php  (monta header + corpo + footer)
       └─ notifications/email.blade.php  (conteúdo de notificações)

    SLOTS RECEBIDOS DE message.blade.php:
    - $head   : conteúdo adicional para <head> (meta tags, fontes externas)
    - $header : componente <x-mail::header> com logo/nome do app
    - $slot   : corpo do e-mail em HTML (já convertido do Markdown)
    - $subcopy: rodapé alternativo com URL fallback para botões bloqueados
    - $footer : componente <x-mail::footer> com copyright

    ESTILOS:
    O CSS injetado via {!! $head ?? '' !!} vem do arquivo themes/default.css.
    O Laravel também faz "CSS inlining" automaticamente, os estilos são movidos
    para atributos style="" inline antes do envio, garantindo compatibilidade
    com clientes de e-mail que bloqueiam stylesheets externas.

    PARA ADICIONAR FONTES EXTERNAS (ex: Google Fonts):
    Injete via <x-slot:head> no message.blade.php:
        <x-slot:head>
            <link href="https://fonts.googleapis.com/css2?family=Inter" rel="stylesheet">
        </x-slot:head>
    Atenção: muitos clientes de e-mail bloqueiam fontes externas.
    Sempre defina uma fonte fallback no CSS (font-family: 'Inter', sans-serif;).

    Pesquise: "Laravel mail layout", "email CSS inlining", "email client support"
--}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 600px) {
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}
</style>
{!! $head ?? '' !!}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<!-- Body content -->
<tr>
<td class="content-cell">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
