{{--
    Componente <x-mail::footer> é rodapé do e-mail.

    Renderizado via <x-slot:footer> em message.blade.php.

    O conteúdo do slot é processado como Markdown então você pode usar
    links, **negrito**, _itálico_ etc.

    PARA CUSTOMIZAR:
    Edite o conteúdo do <x-mail::footer> em message.blade.php:
        <x-mail::footer>
            © {{ date('Y') }} Minha Empresa.
            [Descadastrar]({{ $unsubscribeUrl }}) · [Política de privacidade](https://...)
        </x-mail::footer>

    O estilo visual (cor, tamanho da fonte) está em themes/default.css na seção "Footer".

    Pesquise: "email footer best practices", "CAN-SPAM unsubscribe", "GDPR email"
--}}
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
