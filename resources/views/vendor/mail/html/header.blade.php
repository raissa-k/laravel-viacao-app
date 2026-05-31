{{--
    Componente <x-mail::header> é o cabeçalho do e-mail.

    Renderiza o topo do e-mail com o nome ou logo do app.

    PROPS:
    - $url : URL de destino ao clicar no cabeçalho (geralmente APP_URL)

    LÓGICA EMBUTIDA:
    Se o $slot for exatamente a string "Laravel", exibe o logo oficial do Laravel.
    Caso contrário, exibe o conteúdo do slot como texto/HTML.
    Como passamos {{ config('app.name') }} em message.blade.php,
    o cabeçalho exibirá o nome do app como texto clicável.

    PARA ADICIONAR UM LOGO:
    Substitua o conteúdo do slot em message.blade.php por uma tag <img>:
        <x-mail::header :url="config('app.url')">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" height="40">
        </x-mail::header>

    Atenção: e-mails devem usar imagens hospedadas em URL pública (não asset() local), pois o cliente de e-mail precisa buscar a imagem do servidor.

    Pesquise: "email logo best practices", "hosted images email", "CID inline images"
--}}
@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
