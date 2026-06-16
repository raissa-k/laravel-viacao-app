<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\Pipelines\FiltroCategoria;
use App\Actions\Pipelines\FiltroDiaSemana;
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
        // PASSO 1: PADRONIZAÇÃO DE DADOS (Hidratação)
        // Transformamos o array "sujo" que veio da API/Banco em Objetos seguros (DTOs).
        $collectionDeDTOs = collect($linhas)->map(function (mixed $linha) {
            return $linha instanceof LinhaResultadoDTO
                ? $linha
                : LinhaResultadoDTO::fromArray((array) $linha);
        });

        // PASSO 2: A LINHA DE MONTAGEM (Pipeline Pattern)
        // Pedimos para o Laravel instanciar a classe de Pipeline nativa dele.
        $resultado        = app(Pipeline::class)
            ->send($collectionDeDTOs) // O que vai entrar no começo do tubo? Nossos DTOs.
            ->through([               // Por quais canos (filtros) essa coleção vai passar?

                // Os filtros serão executados EXATAMENTE nesta ordem:
                new FiltroCategoria($categoria),
                new FiltroDiaSemana($dia),

                // (Se no futuro precisarmos de um Filtro de Preço, é só adicionar aqui!)
            ])
            ->thenReturn();           // Me devolva o que sobrar no final do último cano.

        // PASSO 3: LIMPEZA FINAL
        // O ->values() serve para resetar os índices do array.
        // Ex: Se o item [0] e [2] foram removidos no filtro, o ->values()
        // reorganiza tudo bonitinho como [0], [1], etc.
        return $resultado->values();
    }
}
