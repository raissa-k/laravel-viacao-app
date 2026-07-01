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
        public bool $chegaDiaSeguinte = false,
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
        $id               = (int) ($dados['id'] ?? 0);

        try {
            $partida         = Carbon::parse((string) ($dados['partida'] ?? '00:00'))->format('H:i');
        } catch (\Throwable $e) {
            $partida         = '00:00';
        }

        $horasDuracao     = $duracaoMinutos > 0 ? (int) ceil($duracaoMinutos / 60) : 1;

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

        $categoria        = Categoria::tryFrom((string)($dados['tipo']??'')) ?? Categoria::Convencional;
        $assentos         = max(0, (int) ($dados['assentos'] ?? 0));
        $diasDaSemana     = (array) ($dados['diasDaSemana'] ?? []);

        $precoMinimo      = isset($dados['preco_min']) ? (float) $dados['preco_min'] : $precoMinimo;
        $precoMaximo      = isset($dados['preco_max']) ? (float) $dados['preco_max'] : $precoMaximo;

        try {
            $vPartida        = Carbon::createFromFormat('H:i', $dados['partida'] ?? '00:00'); //obj carbon puro
        } catch (\Throwable $e) {
            $vPartida        = Carbon::createFromFormat('H:i', '00:00'); //obj carbon puro
        }

        try {
            $vChegada        = Carbon::createFromFormat('H:i', $dados['chegada_estimada'] ?? '00:00'); //obj carbon
        } catch (\Throwable $e) {
            $vChegada        = (clone $vPartida)->addHours($horasDuracao); //obj carbon
        }

        $partida          = $vPartida->format('H:i'); //string em si já formatada
        $chegada          = $vChegada->format('H:i'); //string em si já formatada
        $chegaDiaSeguinte = false;

        //bloco de lógica para tratamento de horários inválidos (o +1 que chega no dia seguinte está sendo tratado no blade resources/views/components/horario-card.blade.php.)
        if ($vChegada->lessThan($vPartida)) {
            //partidas noturnas >=18 com chegadas de manha <= 12h são aceitas como dia seguinte
            if ($vPartida->hour >= 18 && $vChegada->hour <= 12) {
                $chegaDiaSeguinte = true;
            } else {
                // se for um dado ruim ele aparece a string
                $chegaDiaSeguinte = true;
            } // não sei se prescisa de else, mas deixei por precaução
        }

        return new self(
            id: $id,
            partida: $partida,
            chegada: $chegada,
            chegaDiaSeguinte: $chegaDiaSeguinte,
            categoria: $categoria,
            assentos: $assentos,
            diasDaSemana: $diasDaSemana,
            precoMinimo: $precoMinimo,
            precoMaximo: $precoMaximo,
        );
    }
}
