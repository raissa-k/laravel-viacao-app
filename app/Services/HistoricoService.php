<?php

// - when() substitui o array $wheres + implode(' AND ', $wheres)
// - with() resolve o N+1 problem: carrega viacao e usuario em 2 queries IN, não 1 por registro
// - whereHas() gera sub-selects EXISTS pra buscar por campos das relações (sem JOIN)

namespace App\Services;

use App\DTOs\HistoricoFilterDTO;
use App\Models\ViacaoHistorico;
use Illuminate\Database\Eloquent\Collection;

class HistoricoService
{
    /**
     * Retorna registros com filtros encapsulados no DTO.
     * O DTO já validou datas e normalizou strings antes de chegar no service.
     *
     * EAGER LOADING vs LAZY LOADING:
     * - Eager (with()): carrega relacionamentos COM a query principal
     * Usado quando você SABE que vai acessar os relacionamentos
     * - Lazy (acesso direto): carrega relacionamentos QUANDO acessado
     * 1 query por acesso = N+1 problem
     *
     * Exemplo da vida real:
     * Sem with() numa view com 50 históricos:
     * - 1 query: SELECT * FROM viacoes_historico
     * - 50 queries: SELECT * FROM viacoes WHERE id = ? (uma por histórico)
     * - 50 queries: SELECT * FROM usuarios WHERE id = ? (uma por histórico)
     * = 101 queries! AAAAAA!
     *
     * Com with():
     * - 1 query: SELECT * FROM viacoes_historico
     * - 1 query: SELECT * FROM viacoes WHERE id IN (1,2,3,...,50)
     * - 1 query: SELECT * FROM usuarios WHERE id IN (1,2,3,...,50)
     * = 3 queries! Yaaaay!
     *
     * Pesquise: "Eloquent eager loading", "N+1 query problem", "query optimization".
     */
    public function getHistory(HistoricoFilterDTO $filter = new HistoricoFilterDTO): Collection
    {
        return ViacaoHistorico::with(['viacao', 'usuario'])
            /*
             * with() executa eager loading. Carrega viacao e usuario usando IN clause, não 1 query por registro (que seria N+1 problem).
             *
             * No PHP puro: HistoricoRepository usava LEFT JOIN para trazer tudo em 1 query.
             * No Laravel: with() usa IN clause, 2 queries extras, mas mesmo resultado.
             * Ambas abordagens evitam o N+1, por caminhos diferentes.
             */
            ->when($filter->viacaoId, fn ($q) => $q->where('viacao_id', $filter->viacaoId))
            /*
             * $filter->acao é ?AcaoHistorico (enum), não ?string.
             * when() só executa o callback quando o valor é truthy (não null) porque às vezes não estamos filtrando nada.
             * ->value extrai a string 'Criado'/'Editado'/'Excluido' pro WHERE já o banco armazena string.
             * Pesquise "Backed enums", "type safety".
             */
            ->when($filter->acao, fn ($q) => $q->where('acao', $filter->acao->value))
            ->when($filter->dateFrom, fn ($q) => $q->where('criado_em', '>=', $filter->dateFrom.' 00:00:00'))
            ->when($filter->dateTo, fn ($q) => $q->where('criado_em', '<=', $filter->dateTo.' 23:59:59'))
            ->when($filter->q !== '', function ($q) use ($filter) {
                /*
                 * ATENÇÃO: POR QUE NÃO PROCURAMOS NO JSON DE ALTERAÇÕES?
                 *
                 * 1. PERFORMANCE EM AUDIT TABLES:
                 * - Audit tables (historico) crescem rápido: cada alteração em qualquer linha de dados gera um novo registro aqui.
                 * - Casting JSON -> CHAR ou JSON_SEARCH executam escans sequenciais em TODA a coluna.
                 * - Resultado: full table scan em milhões de registros = query muito longa.
                 * - Indexar campos JSON é uma alternativa que existe, sim, mas não vale a pena para o que queremos aqui.
                 *
                 * 2. INTENÇÃO DO CAMPO ALTERACOES:
                 * - "alteracoes" é LOG: documenta o QUÊ mudou, não é um campo de negócio pra filtrar.
                 * - Você filtra por Viação, Ação, Data: dados estruturados e indexados.
                 * - Você NÃO filtra por "qual valor antigo existia dentro do JSON": seria buscar em log, não em dados estruturados.
                 * - Se precisa disso, melhor extrair o campo lógico pra uma coluna separada e indexe.
                 *
                 * 3. PROBLEMAS COM JSON_SEARCH:
                 * - JSON_SEARCH é específico de MySQL/PostgreSQL: SQLite não suporta bem pra conseguir atuar nos testes do nosso projeto.
                 * - CAST(json AS CHAR) LIKE funciona mas é MUITO lento em dados grandes.
                 * - Ambos contornam o design: "estou tentando usar JSON como se fosse texto".
                 *
                 * 4. O PADRÃO CERTO:
                 * - Use whereHas() + índices em colunas estruturadas (viacao.nome, usuario.nome).
                 * - Deixe JSON como exibição apenas, você lê (carrega) mas não filtra.
                 * - Se um campo do log precisa ser queryable, extraia pra coluna.
                 *
                 * Existem ferramentas melhores pra fazer buscas como essa, caso necessário (ElasticSearch).
                 * CONCLUSÃO: Filtramos por viacao/usuario (dados estruturados). JSON fica como audit log visualizável.
                 * Pesquise: "audit tables performance", "JSON anti-patterns", "indexing JSON in MySQL".
                 */
                $escaped = addcslashes($filter->q, '%_'); // aqui para permitir que busquemos por "100%", por exemplo
                $q->where(function ($q2) use ($escaped) {
                    $q2->whereHas('viacao', fn ($r) => $r->where('nome', 'like', '%'.$escaped.'%'))
                        ->orWhereHas('usuario', fn ($r) => $r->where('nome', 'like', '%'.$escaped.'%'));
                });
            })
            ->orderByDesc('criado_em')
            ->get();
    }
}
