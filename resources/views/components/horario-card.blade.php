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
                                        /*
                                         * Carbon::parse() com o nome do dia em pt-BR não funciona diretamente.
                                         * Solução: mapeia o nome do dia para um número (1=seg ... 7=dom),
                                         * cria uma data Carbon com esse dia da semana e usa translatedFormat()
                                         * com locale pt-BR para obter a abreviação correta.
                                         *
                                         * translatedFormat('D') retorna abreviação de 3 letras no locale configurado:
                                         * 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb', 'dom'
                                         * Pesquise "Carbon translatedFormat", "Carbon locale".
                                         */
                                        $diaNome = [
                                            'segunda' => 1,
                                            'terça'   => 2,
                                            'quarta'  => 3,
                                            'quinta'  => 4,
                                            'sexta'   => 5,
                                            'sábado'  => 6,
                                            'domingo' => 7,
                                        ];
                                        $numero = $diaNome[$dia] ?? null;
                                        $abrev  = $numero
                                            ? ucfirst(Carbon::now()->locale('pt_BR')->startOfWeek()->addDays($numero - 1)->translatedFormat('D'))
                                            : null;
                                    @endphp
                                    @if ($abrev)
                                        <span class="horario-card-dia">{{ $abrev }}</span>
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
