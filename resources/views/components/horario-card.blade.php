@props([
    'horarios' => [],
])

@php
    $diasAbrev = [
        'segunda'  => 'Seg',
        'terça'    => 'Ter',
        'quarta'   => 'Qua',
        'quinta'   => 'Qui',
        'sexta'    => 'Sex',
        'sábado'   => 'Sáb',
        'domingo'  => 'Dom',
    ];
@endphp

<section class="horarios-section">
    <details class="horarios-details" open>
        <summary class="horarios-summary">
            <span class="horarios-titulo">Horários disponíveis</span>
            <span class="horarios-count">{{ count($horarios) }}</span>
            <span class="horarios-toggle-icon" aria-hidden="true"></span>
        </summary>

        <div class="horarios-lista">
            @foreach ($horarios as $h)
                <article class="horario-card">
                    <div class="horario-card-horarios">
                        <div class="horario-card-horario">
                            <span class="horario-card-hora">{{ $h['partida'] }}</span>
                            <span class="horario-card-label">partida</span>
                        </div>
                        <div class="horario-card-horario">
                            <span class="horario-card-hora">{{ $h['chegada'] }}</span>
                            <span class="horario-card-label">chegada</span>
                        </div>
                    </div>

                    <div class="horario-card-info">
                        @if (!empty($h['categoria']))
                            <x-badge
                                rotulo="{{ $h['categoria']->rotulo() }}"
                                tipo="{{ $h['categoria']->tipoBadge() }}"
                            />
                        @endif

                        @if (!empty($h['assentos']))
                            <span class="horario-card-assentos">{{ $h['assentos'] }} assentos</span>
                        @endif

                        @if (!empty($h['dias']))
                            <div class="horario-card-dias">
                                @foreach ($h['dias'] as $dia)
                                    @if (isset($diasAbrev[$dia]))
                                        <span class="horario-card-dia">{{ $diasAbrev[$dia] }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if (!empty($h['preco']))
                        <div class="horario-card-preco">
                            <span class="horario-card-preco-valor">R$ {{ number_format($h['preco'], 2, ',', '.') }}</span>
                            @if (!empty($h['precoMax']))
                                <span class="horario-card-preco-max">até R$ {{ number_format($h['precoMax'], 2, ',', '.') }}</span>
                            @endif
                            <a class="horario-card-btn" href="#">Comprar</a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </details>
</section>
