<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\Pipelines\FiltroCategoria;
use App\Actions\Pipelines\FiltroDiaSemana;
use App\Actions\Pipelines\HidratarOperadorasEDTOs;
use App\DTOs\LinhaResultadoDTO;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

/**
 * A Action principal que orquestra todo o processo de filtragem.
 * Ela não aplica as regras de negócio diretamente; o trabalho dela é
 * preparar os dados e enviá-los para a nossa Pipeline (linha de montagem).
 */
class FiltrarLinhas
{
    public function execute(array $linhas, ?string $categoria = null, ?string $dia = null): Collection
    {
        // PASSO 1: PREPARAÇÃO DA COLEÇÃO BRUTA
        // Criamos uma Collection diretamente com os arrays brutos enviados pela API.
        // Se um DTO for enviado incorretamente aqui, o fluxo quebrará mais adiante,
        // garantindo o cumprimento estrito do contrato da assinatura do método.
        $collectionDeDTOs = collect($linhas)->map(function (array $linha) {
            return LinhaResultadoDTO::fromArray($linha);
        });

        // PASSO 2: A LINHA DE MONTAGEM (Pipeline Pattern)
        // Pedimos para o Laravel instanciar a classe de Pipeline nativa dele.
        $resultado        = app(Pipeline::class)
            ->send($collectionDeDTOs)  // Envia os dados brutos da API para a pipeline.
            ->through([               // Por quais canos (filtros) essa coleção vai passar?

                // Os filtros serão executados EXATAMENTE nesta ordem:
                new FiltroCategoria($categoria),
                new FiltroDiaSemana($dia),

                // ÚLTIMA ETAPA: Após os filtros, hidratamos as operadoras e criamos os DTOs.
                // Dessa forma consultamos apenas as operadoras das linhas que
                // realmente permaneceram após a filtragem, evitando consultas
                // desnecessárias ao banco de dados.
                new HidratarOperadorasEDTOs(),

                // (Se no futuro precisarmos de um Filtro de Preço, é só adicionar aqui!)
            ])
            ->thenReturn();            // Me devolva o que sobrar no final do último cano.

        // PASSO 3: LIMPEZA FINAL
        // O ->values() serve para resetar os índices da Collection.
        return $resultado->values();
    }
}
