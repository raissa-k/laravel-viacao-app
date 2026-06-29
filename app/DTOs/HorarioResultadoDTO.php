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
    public static function fromArray(array $dados, float $precoMinimo, ?float $precoMaximo = null): ?self
    {
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
                $chegada .= ' *saber mais* ';
            }
        }
        //------------------------------------------
        $id              = (int) ($dados['id'] ?? 0);
        $categoria       = Categoria::tryFrom((string)($dados['tipo']??'')) ?? Categoria::Convencional;
        $assentos        = (int) ($dados['assentos'] ?? 0);
        $diasDaSemana    = (array) ($dados['diasDaSemana'] ?? []);

        $precoMinimo     = isset($dados['preco_min']) ? (float) $dados['preco_min'] : $precoMinimo;
        $precoMaximo     = isset($dados['preco_max']) ? (float) $dados['preco_max'] : $precoMaximo;

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
