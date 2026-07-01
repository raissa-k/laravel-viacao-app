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
                            <span class="horario-card-hora">{{ $h->partida }}</span>
                            <span class="horario-card-label">partida</span>
                        </div>
                        <div class="horario-card-horario">
                            <span class="horario-card-hora horario-chegada">
                                {{ $h->chegada }}
                                @if($h->chegaDiaSeguinte)
                                    <span class="day-offset">+1<span class="day-tooltip">Chegada no dia seguinte</span>
                                    </span>
                                @endif</span>
                            <span class="horario-card-label">chegada</span>
                        </div>
                    </div>

                    <div class="horario-card-info">
                        @if (!empty($h->categoria))
                            <x-badge
                                rotulo="{{ $h->categoria->rotulo() }}"
                                tipo="{{ $h->categoria->tipoBadge() }}"
                            />
                        @endif

                            @if(isset($h->assentos) && $h->assentos > 0)
                                <span class="horario-card-assentos">{{ $h->assentos }} assentos disponíveis</span>
                            @else
                                <span class="horario-card-assentos esgotado">Esgotado</span>
                            @endif

                        @if (!empty($h->dias))
                            <div class="horario-card-dias">
                                @foreach ($h->dias as $dia)
                                    {{-- dias_semana vem como string pt_BR da API ("segunda", "sábado", etc).
                                         parseFromLocale interpreta o nome no locale pt_BR e
                                         getTranslatedShortDayName retorna a abreviação traduzida. --}}
                                    @php
                                        try {
                                            $abrev = ucfirst(
                                                Carbon::parseFromLocale(mb_strtolower($dia), 'pt_BR')
                                                    ->locale('pt_BR')
                                                    ->getTranslatedShortDayName()
                                            );
                                        } catch (\Throwable $e) {
                                            $abrev = ucfirst($dia);
                                        }
                                    @endphp
                                    <x-badge rotulo="{{ $abrev }}" tipo="dias-da-semana" />
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if (!empty($h->preco))
                        <div class="horario-card-preco">
                            <div class="horario-card-preco-valores">
                                <span class="horario-card-preco-valor">R$ {{ number_format($h->preco, 2, ',', '.') }}</span>
                                @if (!empty($h->precoMax))
                                    <span class="horario-card-preco-max">até R$ {{ number_format($h->precoMax, 2, ',', '.') }}</span>
                                @endif
                            </div>
                            <a class="horario-card-btn" href="#">Comprar</a>

                            Expand Down

                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </details>
</section>
