<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\Pipelines\FiltroCategoria;
use App\Actions\Pipelines\FiltroDiaSemana;
use App\DTOs\LinhaResultadoDTO;
use App\Models\Viacao;
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
        // PASSO 1: RESOLUÇÃO DE NOMES E HIDRATAÇÃO (Evita N+1)
        // 1. Coleta os IDs únicos das operadoras diretamente do array de entrada
        $operadoraIds     = collect($linhas)->pluck('operadora_id')->unique()->filter()->toArray();

        // 2. Busca todas as viações de uma vez só no banco de dados local
        $viacoesLocais    = Viacao::whereIn('api_id', $operadoraIds)->get()->keyBy('api_id');

        // 3. Transforma o array "sujo" que veio da API em DTOs seguros já com o nome resolvido
        $collectionDeDTOs = collect($linhas)->map(function (array $item) use ($viacoesLocais) {
            $viacao                 = $viacoesLocais->get($item['operadora_id'] ?? null);

            // Define o nome esperado pela chave do array que o seu DTO consome
            $item['operadora_nome'] = $viacao ? $viacao->nome : 'Operadora Desconhecida';

            return LinhaResultadoDTO::fromArray($item);
        });

        // PASSO 2: A LINHA DE MONTAGEM (Pipeline Pattern)
        // Pedimos para o Laravel instanciar a classe de Pipeline nativa dele.
        $resultado        = app(Pipeline::class)
            ->send($collectionDeDTOs)
            ->through([               // Por quais canos (filtros) essa coleção vai passar?

                // Os filtros serão executados EXATAMENTE nesta ordem:
                new FiltroCategoria($categoria),
                new FiltroDiaSemana($dia),

                // (Se no futuro precisarmos de um Filtro de Preço, é só adicionar aqui!)
            ])
            ->thenReturn();           // Me devolva o que sobrar no final do último cano.

        // PASSO 3: LIMPEZA FINAL
        // O ->values() serve para resetar os índices do array.
        return $resultado->values();
    }
}
