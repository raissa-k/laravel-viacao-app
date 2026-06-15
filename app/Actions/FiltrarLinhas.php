<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\LinhaResultadoDTO;
use Illuminate\Support\Collection;

class FiltrarLinhas
{
    /**
     * Executa o filtro sobre uma lista de linhas de ônibus.
     *
     * @param  array                              $linhas
     * @param  string|null                        $categoria
     * @param  string|null                        $dia
     * @return Collection<int, LinhaResultadoDTO>
     */
    public function execute(array $linhas, ?string $categoria = null, ?string $dia = null): Collection
    {
        // 1. Converter cada item do array bruto em LinhaResultadoDTO
        $resultado = collect($linhas)->map(function (mixed $linha) {
            return $linha instanceof LinhaResultadoDTO
                ? $linha
                : LinhaResultadoDTO::fromArray((array) $linha);
        });

        // 2. Filtrar por $categoria (quando não-null)
        if ($categoria !== null) {
            $catNormalizada = mb_strtolower($categoria, 'UTF-8');
            $resultado      = $resultado->filter(
                fn (LinhaResultadoDTO $dto) =>
                $dto->categoria?->value === $catNormalizada
            );
        }

        // 3. Filtrar por $dia (quando não-null)
        if ($dia !== null) {
            $diaNormalizado = mb_strtolower($dia, 'UTF-8');
            $resultado      = $resultado->filter(
                fn (LinhaResultadoDTO $dto) =>
            in_array($diaNormalizado, $dto->diasDaSemana, true)
            );
        }

        // Retorna a collection reindexada
        return $resultado->values();
    }
}
