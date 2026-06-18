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
        // 1. Coleta os IDs suportando dinamicamente Array (operadora_id) ou Objeto/DTO antigo do teste
        $operadoraIds   = $linhasFiltradas->map(function (mixed $item) {
            return is_array($item) ? ($item['operadora_id'] ?? null) : ($item->operadoraId ?? null);
        })->unique()->filter()->toArray();

        // 2. Busca as viações no banco de dados (Query unificada)
        $viacoesLocais  = Viacao::whereIn('api_id', $operadoraIds)->get()->keyBy('api_id');

        // 3. Mapeia DIRETAMENTE para o DTO. Zero "instanceof LinhaResultadoDTO" aqui dentro!
        $dtosHidratados = $linhasFiltradas->map(function (mixed $item) use ($viacoesLocais) {

            // Se for um objeto (teste antigo), normaliza para array mapeando chaves
            if (is_object($item)) {
                $dados                 = get_object_vars($item);
                $dados['operadora_id'] = $item->operadoraId       ?? null;
                $dados['categoria']    = $item->categoria?->value ?? $item->categoria ?? null;
                $dados['dias_semana']  = $item->diasDaSemana      ?? [];
            } else {
                $dados = (array) $item;
            }

            // Injeta o nome da viação
            $viacao                  = $viacoesLocais->get($dados['operadora_id'] ?? null);
            $dados['operadora_nome'] = $viacao ? $viacao->nome : 'Operadora Desconhecida';

            // Mapeamento direto pro DTO final
            return LinhaResultadoDTO::fromArray($dados);
        });

        return $next($dtosHidratados);
    }
}
