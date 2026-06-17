@props([
    'horarios' => [],
])

@use('Carbon\Carbon')

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
                                   @php
                                        $abrev = match($dia) {
                                            'segunda' => Carbon::now()->startOfWeek()->translatedFormat('D'),
                                            'terça'   => Carbon::now()->startOfWeek()->addDays(1)->translatedFormat('D'),
                                            'quarta'  => Carbon::now()->startOfWeek()->addDays(2)->translatedFormat('D'),
                                            'quinta'  => Carbon::now()->startOfWeek()->addDays(3)->translatedFormat('D'),
                                            'sexta'   => Carbon::now()->startOfWeek()->addDays(4)->translatedFormat('D'),
                                            'sábado'  => Carbon::now()->startOfWeek()->addDays(5)->translatedFormat('D'),
                                            'domingo' => Carbon::now()->startOfWeek()->addDays(6)->translatedFormat('D'),
                                            default   => null,
                                        };
                                    @endphp
                                    @if ($abrev)
                                        <span class="horario-card-dia">{{ ucfirst($abrev) }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if (!empty($h['preco']))
                        <div class="horario-card-preco">
                            <div class="horario-card-preco-valores">
                                <span class="horario-card-preco-valor">R$ {{ number_format($h['preco'], 2, ',', '.') }}</span>
                                @if (!empty($h['precoMax']))
                                    <span class="horario-card-preco-max">até R$ {{ number_format($h['precoMax'], 2, ',', '.') }}</span>
                                @endif
                            </div>
                            <a class="horario-card-btn" href="#">Comprar</a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </details>
</section>
