<?php

declare(strict_types=1);

namespace App\Actions\Pipelines;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * ESTÁGIO 2 DA PIPELINE (Linha de Montagem)
 * * Este é o segundo funcionário da esteira. Ele recebe a caixa (que pode já
 * ter sido filtrada pelo FiltroCategoria antes) e aplica a sua própria regra
 * de negócio isolada.
 */
class FiltroDiaSemana
{
    public function __construct(protected ?string $dia)
    {
    }

    public function handle(Collection $linhas, Closure $next)
    {
        // 1. Se o cliente não informou o dia, não temos o que fazer.
        // Apenas passamos o bastão adiante.
        if ($this->dia === null) {
            return $next($linhas);
        }

        // 2. Padroniza a busca (ex: transforma 'SáBaDo' em 'sábado')
        $diaNormalizado  = Str::lower($this->dia);

        // 3. Filtramos a Collection para manter apenas as linhas que operam neste dia.
        $linhasFiltradas = $linhas->filter(
            fn ($dto) =>
                // Usamos o 'true' no in_array (strict mode) para evitar comportamentos
                // bizarros do PHP. Ele garante que o tipo e o valor sejam exatos.
            in_array($diaNormalizado, $dto->diasDaSemana, true)
        );

        // 4. Passa o resultado para o próximo da fila (ou finaliza, se não houver mais filtros).
        return $next($linhasFiltradas);
    }
}
