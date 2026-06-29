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

        try {
            $partida         = Carbon::parse((string) ($dados['partida'] ?? '00:00'))->format('H:i');
        } catch (\Throwable $e) {
            $partida         = '00:00';
        }

        // Converte os minutos vindos da API para horas arredondadas (ex: 360min -> 6h)
        $horasDuracao    = $duracaoMinutos > 0 ? (int) ceil($duracaoMinutos / 60) : 1;

        try {
            $chegada         = Carbon::parse((string) ($dados['chegada_estimada'] ?? '00:00'))->format('H:i');

            // Se a chegada for menor ou igual à partida, calculamos a diferença simulando a virada de dia
            if (Carbon::parse($chegada)->lessThanOrEqualTo(Carbon::parse($partida))) {
                // Exemplo: sai 22:00 e chega 04:00 (Soma 1 dia na chegada para dar +6 horas de viagem)
                $diffHoras = Carbon::parse($partida)->diffInHours(Carbon::parse($chegada)->addDay());

                // Se a diferença calculada for bizarra em relação à duração esperada da linha (tolerância de 2h)
                // significa que o dado da API está corrompido (ex: sai 12:00 e chega 11:00 numa viagem de 17h)
                if ($duracaoMinutos > 0 && abs($diffHoras - $horasDuracao) > 2) {
                    $chegada = Carbon::parse($partida)->addHours($horasDuracao)->format('H:i');
                }
            }
        } catch (\Throwable $e) {
            $chegada         = Carbon::parse($partida)->addHours($horasDuracao)->format('H:i');
        }

        $categoria       = Categoria::tryFrom((string)($dados['tipo']??'')) ?? Categoria::Convencional;

        // Garante que assentos negativos vindos da API (como o -12) virem pelo menos 0
        $assentos        = max(0, (int) ($dados['assentos'] ?? 0));

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
//Aqui eu estabeleci o HorarioResultadoDTO e configurei o __construct. Na propriedade categoria, em específico, o DTO define que o objeto receberá o Enum Categoria.
//Os dados brutos chegam através do parâmetro de array $dados. A partir dele, eu cato o resto das informações que serão tratadas. No caso da categoria, o código pega a string contida na chave 'tipo' da API e a armazena na variável $categoria.
// Se não vier nada ou o valor for inválido, entra em ação um fallback padrão que receberá Categoria::Convencional.
//Em precoMinimo e precoMaximo, usa o isset para verificar se os campos existem e não são nulos.
// Se não forem nulos, ele colocará os dados corretos vindos da API. Caso contrário, ativa o Fallback e resgata os preços padrões recebidos por parâmetro.
