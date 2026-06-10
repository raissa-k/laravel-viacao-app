<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\HistoricoFilterDTO;
use App\Models\Historico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HistoricoService
{
    /**
     * Retorna registros com filtros encapsulados no DTO.
     *
     * EAGER LOADING:
     * 'entidade' resolve para Viacao ou Usuario dependendo de entidade_type,
     * o ORM polimórfico faz isso em 2 queries IN (uma por tipo presente na página), não 1 query por linha.
     *
     * BUSCA com whereHasMorph():
     * whereHas() não funciona em morphTo porque a tabela relacionada varia por tipo.
     * whereHasMorph('entidade', '*', callback) gera um EXISTS por tipo registrado no morph map. Neste caso, dois EXISTS (viacoes + usuarios).
     * O '*' significa "todos os tipos do morph map".
     * Como where('entidade_type') já filtra uma aba de cada vez, apenas um dos EXISTS contribui com resultados.
     * Pesquise "whereHasMorph Laravel", "polymorphic whereHas".
     *
     * Por que não pesquisar no JSON de alteracoes?
     * Audit tables crescem rápido: JSON_SEARCH e CAST->LIKE viram full table scans.
     * Filtramos por campos estruturados (nome, acao, datas) e deixamos o JSON como exibição apenas.
     * Pesquise "audit tables performance", "JSON anti-patterns".
     */
    public function getHistory(HistoricoFilterDTO $filter = new HistoricoFilterDTO()): LengthAwarePaginator
    {
        return Historico::with(['entidade', 'ator'])
            ->where('entidade_type', $filter->entidade->value)
            ->when($filter->acao, fn ($q) => $q->where('acao', $filter->acao->value))
            ->when($filter->dateFrom, fn ($q) => $q->where('criado_em', '>=', $filter->dateFrom.' 00:00:00'))
            ->when($filter->dateTo, fn ($q) => $q->where('criado_em', '<=', $filter->dateTo.' 23:59:59'))
            ->when($filter->q !== '', function ($q) use ($filter) {
                $escaped = addcslashes($filter->q, '%_'); // aqui para permitir que busquemos por "100%", por exemplo
                // neste caso, Viacao e Usuario usam a mesma coluna 'nome'
                $q->whereHasMorph('entidade', '*', fn ($r) => $r->where('nome', 'like', '%'.$escaped.'%'));
            })
            ->orderByDesc('criado_em')
            ->paginate(15)
            ->withQueryString();
    }
}
