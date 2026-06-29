<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Categoria;
use Carbon\Carbon;

final readonly class HorarioResultadoDTO
{
    public function __construct(
        public int    $id,
        public string $partida,
        public string $chegada,
        public Categoria $categoria, //aqui é o uso do Enum
        public int $assentos,
        public array  $diasDaSemana,
        public float $precoMinimo,
        public ?float $precoMaximo,
    ) {
    }


    /** Constrói o DTO a partir do array bruto devolvido pela API. */
    public static function fromArray(array $dados, float $precoMinimo, ?float $precoMaximo = null, int $duracaoMinutos = 0): self
    {
        $id              = (int) ($dados['id'] ?? 0);
        $partida         = Carbon::parse((string) ($dados['partida'] ?? '00:00'))->format('H:i');
        $chegada         = Carbon::parse((string) ($dados['chegada_estimada'] ?? '00:00'))->format('H:i');

        try {
            $partida         = Carbon::parse((string) ($dados['partida'] ?? '00:00'))->format('H:i');
        } catch (\Throwable $e) {
            $partida         = '00:00';
        }

        $horasDuracao    = $duracaoMinutos > 0 ? (int) ceil($duracaoMinutos / 60) : 1;

        try {
            $chegada         = Carbon::parse((string) ($dados['chegada_estimada'] ?? '00:00'))->format('H:i');

            if (Carbon::parse($chegada)->lessThanOrEqualTo(Carbon::parse($partida))) {
                $diffHoras = Carbon::parse($partida)->diffInHours(Carbon::parse($chegada)->addDay());

                if ($duracaoMinutos > 0 && abs($diffHoras - $horasDuracao) > 2) {
                    $chegada = Carbon::parse($partida)->addHours($horasDuracao)->format('H:i');
                }
            }
        } catch (\Throwable $e) {
            $chegada         = Carbon::parse($partida)->addHours($horasDuracao)->format('H:i');
        }

        $categoria       = Categoria::tryFrom((string)($dados['tipo']??'')) ?? Categoria::Convencional;
        $assentos        = max(0, (int) ($dados['assentos'] ?? 0));
        $diasDaSemana    = (array) ($dados['diasDaSemana'] ?? []);

        $precoMinimo     = isset($dados['preco_min']) ? (float) $dados['preco_min'] : $precoMinimo;
        $precoMaximo     = isset($dados['preco_max']) ? (float) $dados['preco_max'] : $precoMaximo;

        $vPartida        = Carbon::createFromFormat('H:i', $dados['partida'] ?? '00:00'); //obj carbon puro
        $vChegada        = Carbon::createFromFormat('H:i', $dados['chegada_estimada'] ?? '00:00'); //obj carbon

        $partida         = $vPartida->format('H:i'); //string em si já formatada
        $chegada         = $vChegada->format('H:i'); //string em si já formatada

        //bloco de lógica para tratamento de horários inválidos
        if ($vChegada->lessThan($vPartida)) {
            //partidas noturnas >=18 com chegadas de manha <= 12h são aceitas como dia seguinte
            if ($vPartida->hour >= 18 && $vChegada->hour <= 12) {
                $chegada .= ' (+1)'; //
            } else {
                // se for um dado ruim ele aparece a string
                $chegada .= ' * ';
            }
        }

        return new self(
            id: $id,
            partida: $partida,
            chegada: $chegada,
            categoria: $categoria,
            assentos: $assentos,
            diasDaSemana: $diasDaSemana,
            precoMinimo: $precoMinimo,
            precoMaximo: $precoMaximo,
        );
    }
}
