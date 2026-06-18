<?php

declare(strict_types=1);

namespace App\Actions\Pipelines;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * ESTÁGIO 1 DA PIPELINE (Linha de Montagem)
 *
 * Pense nesta classe como um funcionário em uma esteira de fábrica.
 * O trabalho dele é pegar a caixa (Collection de linhas), verificar se o cliente
 * pediu para filtrar por "Categoria" e, se sim, jogar fora o que não serve.
 * Depois, ele passa a caixa para o próximo funcionário.
 */
class FiltroCategoria
{
    /**
     * O construtor recebe o parâmetro de filtro que veio lá do Controller/Action.
     */
    public function __construct(protected ?string $categoria)
    {
    }

    /**
     * O método handle é obrigatório em toda classe que participa de uma Pipeline.
     *
     * @param Collection $linhas A coleção de dados brutos ou DTOs atual.
     * @param Closure    $next   A função que "passa o bastão" para o próximo filtro.
     */
    public function handle(Collection $linhas, Closure $next)
    {
        // 1. O cliente não quer filtrar por categoria?
        // Então não fazemos nada! Apenas pegamos os dados e passamos intactos
        // para a próxima classe da fila usando o $next().
        if ($this->categoria === null) {
            return $next($linhas);
        }

        // 2. Se chegou aqui, precisamos filtrar. Primeiro, padronizamos o texto
        // para minúsculo para evitar que 'Leito' e 'leito' sejam tratados diferentes.
        $catNormalizada  = Str::lower($this->categoria);

        // 3. Filtramos a Collection aceitando de forma segura tanto DTOs quanto arrays brutos.
        // Isso mata de vez o erro "Attempt to read property 'categoria' on array".
        $linhasFiltradas = $linhas->filter(function ($item) use ($catNormalizada) {
            $categoria = is_array($item)
                ? ($item['categoria'] ?? null)
                : ($item->categoria?->value ?? null);

            return $categoria === $catNormalizada;
        });

        // 4. Missão cumprida! Passamos o resultado filtrado para o próximo da fila.
        return $next($linhasFiltradas);
    }
}
