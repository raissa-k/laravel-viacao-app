<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\Pipelines\FiltroCategoria;
use App\Actions\Pipelines\FiltroDiaSemana;
use App\Actions\Pipelines\HidratarOperadorasEDTOs;
use App\DTOs\LinhaResultadoDTO;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class FiltrarLinhas
{
    public function execute(array $linhas, ?string $categoria = null, ?string $dia = null): Collection
    {
        // Mapeia diretamente para o DTO na entrada, eliminando o instanceof
        $collectionDeDTOs = collect($linhas)->map(function (mixed $linha) {
            return $linha instanceof LinhaResultadoDTO ? $linha : LinhaResultadoDTO::fromArray((array) $linha);
        });

        // Envia a coleção de DTOs limpos para a esteira
        $resultado        = app(Pipeline::class)
            ->send($collectionDeDTOs)
            ->through([
                new FiltroCategoria($categoria),
                new FiltroDiaSemana($dia),
                new HidratarOperadorasEDTOs(), // Hidratação focada no final
            ])
            ->thenReturn();

        return $resultado->values();
    }
}
