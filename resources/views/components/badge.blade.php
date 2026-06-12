@props(['rotulo', 'tipo' => 'padrao'])
{{--se alguem passar um tipo diferente do que ja tem,ele manda um convencional mesmo,nao quebra--}}

@php
    $tiposConhecidos = ['convencional', 'executivo', 'semi-leito', 'leito', 'success', 'error', 'info', 'warning','padrao'];
    $tipoLimpo = strtolower($tipo);
    $classeTipo = in_array($tipoLimpo, $tiposConhecidos) ? $tipoLimpo : 'convencional';
@endphp
<span {{ $attributes->merge(['class' => "badge badge-{$classeTipo}"]) }}>
    {{ $rotulo }}
</span>
