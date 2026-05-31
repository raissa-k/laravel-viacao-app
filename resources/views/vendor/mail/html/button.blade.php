{{--
    Componente <x-mail::button> é o botão de call-to-action no e-mail.

    Usado em notifications/email.blade.php para renderizar o botão gerado
    por ->action('Texto do botão', 'https://...') no MailMessage.

    PROPS:
    - $url   : URL de destino (obrigatório)
    - $color : 'primary' | 'success' | 'error' (padrão: 'primary')
               Mapeado para .button-primary, .button-success, .button-error no CSS
    - $align : 'center' | 'left' | 'right' (padrão: 'center')

    EXEMPLO DE USO MANUAL EM UM MAILABLE MARKDOWN:
        <x-mail::button url="https://meuapp.com/confirmar" color="success">
            Confirmar e-mail
        </x-mail::button>

    POR QUE TABELAS ANINHADAS?
    Clientes de e-mail antigos (Outlook, Gmail app) não suportam flexbox ou display:block em links.
    A estrutura de tabelas aninhadas é o único padrão garantido entre clientes.
    Pesquise: "email HTML tables", "Outlook email quirks", "email client compatibility".

    PARA CUSTOMIZAR CORES:
    Edite as classes .button-primary, .button-success, .button-error em themes/default.css.
    Os borders (top/bottom/left/right) são truque de padding CSS para e-mail, não remova.

    Pesquise: "email button padding trick", "bulletproof email buttons"
--}}
@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
