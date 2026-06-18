<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\Pipelines\FiltroCategoria;
use App\Actions\Pipelines\FiltroDiaSemana;
use App\Actions\Pipelines\HidratarOperadorasEDTOs;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class FiltrarLinhas
{
    public function execute(array $linhas, ?string $categoria = null, ?string $dia = null): Collection
    {
        $resultado = app(Pipeline::class)
            ->send(collect($linhas)) // Trafega arrays brutos pela esteira
            ->through([
                new FiltroCategoria($categoria),
                new FiltroDiaSemana($dia),
                new HidratarOperadorasEDTOs(), // Onde o DTO finalmente nasce
            ])
            ->thenReturn();

        return $resultado->values();
    }
}
