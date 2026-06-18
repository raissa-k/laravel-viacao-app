<?php

declare(strict_types=1);

namespace App\Actions\Pipelines;

use App\DTOs\LinhaResultadoDTO;
use App\Models\Viacao;
use Closure;
use Illuminate\Support\Collection;

class HidratarOperadorasEDTOs
{
    public function handle(Collection $linhasFiltradas, Closure $next)
    {
        // 1. Coleta os IDs lendo as propriedades tipadas do DTO direto
        $operadoraIds   = $linhasFiltradas->pluck('operadoraId')->unique()->filter()->toArray();

        // 2. Busca as viações no banco de dados rodando apenas 1 query (Evita N+1 de forma otimizada)
        $viacoesLocais  = Viacao::whereIn('api_id', $operadoraIds)->get()->keyBy('api_id');

        // 3. Reconstrói o DTO injetando o nome da operadora mapeado
        $dtosHidratados = $linhasFiltradas->map(function (LinhaResultadoDTO $linha) use ($viacoesLocais) {
            $viacao = $viacoesLocais->get($linha->operadoraId);

            // Abre o DTO e injeta o nome resolvido sem usar instanceof ou checagens dinâmicas
            return LinhaResultadoDTO::fromArray([
                'id'                 => $linha->id                ?? null,
                'numero'             => $linha->numero            ?? null,
                'operadora_id'       => $linha->operadoraId,
                'categoria'          => $linha->categoria?->value ?? $linha->categoria ?? null,
                'dias_semana'        => $linha->diasDaSemana      ?? [],
                'operadora_nome'     => $viacao ? $viacao->nome : 'Operadora Desconhecida',
            ]);
        });

        return $next($dtosHidratados);
    }
}
