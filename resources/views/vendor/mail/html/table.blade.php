{{--
    Componente <x-mail::table> é tabela formatada para e-mail.

    Envolve conteúdo Markdown em um container com estilos de tabela adequados
    para clientes de e-mail. A tabela real é escrita em Markdown dentro do slot.

    EXEMPLO DE USO EM MAILABLE MARKDOWN:
        <x-mail::table>
        | Produto      | Qtd | Preço  |
        | :----------- | :-- | -----: |
        | Passagem SP  | 1   | R$ 150 |
        | Seguro viagem| 1   | R$  30 |
        </x-mail::table>

    OU VIA MailMessage NO toMail():
        return (new MailMessage)
            ->table([
                ['Produto', 'Qtd', 'Preço'],
                ['Passagem SP', '1', 'R$ 150'],
            ]);

    O estilo visual está em themes/default.css na seção "Tables".

    Pesquise: "MailMessage table", "markdown tables email", "email table layout"
--}}
<div class="table">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</div>
