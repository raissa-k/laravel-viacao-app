<?php

declare(strict_types=1);

namespace App\Actions\Pipelines;

use App\DTOs\LinhaResultadoDTO;
use App\Models\Viacao;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HidratarOperadorasEDTOs
{
    public function handle(Collection $linhasFiltradas, Closure $next)
    {
        // Validação: se a collection chegar vazia, apenas passa adiante
        if ($linhasFiltradas->isEmpty()) {
            return $next($linhasFiltradas);
        }

        // 1. Coleta os IDs lendo as propriedades tipadas do DTO direto
        $operadoraIds   = $linhasFiltradas->pluck('operadoraId')->unique()->filter()->toArray();

        // Validação: se nenhum ID foi encontrado, passa adiante sem processamento
        if (empty($operadoraIds)) {
            return $next($linhasFiltradas);
        }

        // 2. Busca as viações no banco de dados rodando apenas 1 query (Evita N+1 de forma otimizada)
        $viacoesLocais  = Viacao::whereIn('api_id', $operadoraIds)->get()->keyBy('api_id');

        // 3. Reconstrói o DTO injetando o nome da operadora mapeado
        $dtosHidratados = $linhasFiltradas->map(function (LinhaResultadoDTO $linha) use ($viacoesLocais) {
            $viacao        = $viacoesLocais->get($linha->operadoraId);

            // Processa o nome da operadora com title case, garantindo formatação consistente
            $operadoraNome = $viacao
                ? Str::title($viacao->nome)
                : 'Operadora Desconhecida';

            return new LinhaResultadoDTO(
                id: $linha->id,
                numero: $linha->numero,
                operadoraId: $linha->operadoraId,
                operadoraNome: $operadoraNome,
                duracao: $linha->duracao,
                duracaoMinutos: $linha->duracaoMinutos,
                precoMinimo: $linha->precoMinimo,
                precoMaximo: $linha->precoMaximo,
                categoria: $linha->categoria,
                diasDaSemana: $linha->diasDaSemana,
            );
        });

        return $next($dtosHidratados);
    }
}
