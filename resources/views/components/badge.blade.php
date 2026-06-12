@props(['rotulo', 'tipo' => 'padrao'])
{{--se alguem passar um tipo diferente do que ja tem,ele manda um convencional mesmo,nao quebra--}}

@php
    $tiposConhecidos = ['padrao','executivo', 'semi-leito', 'leito', 'success', 'error', 'info', 'warning'];
    $tipoLimpo = strtolower($tipo);
    $classeTipo = in_array($tipoLimpo, $tiposConhecidos) ? $tipoLimpo : 'padrao';
@endphp
<span {{ $attributes->merge(['class' => "badge badge-{$classeTipo}"]) }}>
    {{ $rotulo }}
</span>
