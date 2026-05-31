{{--
    Componente <x-mail::panel> é o bloco de destaque com borda lateral.

    Usado para chamar atenção para informações importantes dentro do e-mail.
    Renderizado como um card com borda esquerda colorida e fundo levemente diferente.

    EXEMPLO DE USO EM MAILABLE MARKDOWN:
        <x-mail::panel>
        Seu código de confirmação é: **{{ $codigo }}**
        </x-mail::panel>

    OU VIA MailMessage NO toMail():
        return (new MailMessage)
            ->greeting('Olá!')
            ->line('Recebemos seu pedido.')
            ->panel('Código de rastreamento: ' . $this->codigo);

    O estilo visual está em themes/default.css nas seções "Panels".
    Para customizar a cor da borda, edite .panel { border-left-color }.

    Pesquise: "MailMessage panel", "email call-to-action design"
--}}
<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-content">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-item">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
</table>
