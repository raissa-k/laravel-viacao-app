<?php

// Service de viações: regra de negócio de criação, edição, exclusão e registro de histórico.
// PHP puro: PDO + beginTransaction() + prepare() + execute()
// Laravel:  Eloquent + DB::transaction() + Model::create() + $model->update()

namespace App\Services;

use App\DTOs\ViacaoFilterDTO;
use App\Enums\AcaoHistorico;
use App\Models\Viacao;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ViacaoService
{
    public function __construct(
        private readonly UploadService $uploadService,
    ) {}

    /**
     * Retorna viações paginadas com filtros.
     *
     * onlyTrashed() vs withTrashed():
     * - onlyTrashed(): WHERE deleted_at IS NOT NULL  -> só excluídos
     * - withTrashed(): sem filtro em deleted_at       -> todos (ativos + excluídos)
     * - padrão (sem nenhum): WHERE deleted_at IS NULL -> só ativos
     *
     * Aqui: deletado=true mostra APENAS excluídos (para ação de restaurar).
     * O usuário filtra entre "ativos" e "excluídos" explicitamente, nunca mistura.
     */
    public function all(ViacaoFilterDTO $filter = new ViacaoFilterDTO): LengthAwarePaginator
    {
        return Viacao::query()
            ->when($filter->deletado, fn ($q) => $q->onlyTrashed())
            ->when($filter->q !== '', function ($query) use ($filter) {
                // addcslashes escapa % e _ que o MySQL interpreta como wildcards no LIKE.
                // Sem isso, "100%" no campo de busca viraria "qualquer coisa começando com 100".
                // Pesquise "SQL LIKE wildcards", "ESCAPE clause".
                $escaped = addcslashes($filter->q, '%_');
                $query->where(function ($q2) use ($escaped) {
                    $q2->where('nome', 'like', '%'.$escaped.'%')
                        ->orWhereHas('cidade', fn ($q3) => $q3->where('nome', 'like', '%'.$escaped.'%'));
                });
            })
            ->when($filter->ativa !== null, fn ($query) => $query->where('ativa', $filter->ativa))
            ->with('cidade') // eager loading pra otimizar queries
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString(); // preserva ?q=...&ativa=... nos links de paginação
    }

    /** Retorna só as viações ativas. Usada na home pública. */
    public function active(): Collection
    {
        return Viacao::query()->where('ativa', true)->with('cidade')->orderByDesc('id')->get();
    }

    /** Busca uma viação pelo ID. Retorna null se não encontrar. */
    public function find(int $id): ?Viacao
    {
        return Viacao::with('cidade')->find($id);
    }

    /**
     * Cria uma nova viação e registra no histórico.
     *
     * DB::transaction() com closure: padrão moderno do Laravel
     * - Abre transação, executa a closure, commit automático se tudo der certo
     * - Se uma exception for lançada dentro, rollback automático
     * - Não precisa try/catch explícito, o rollback é garantido
     *
     * Pesquise: "Laravel DB::transaction", "closure-based transactions", "ACID guarantees".
     */
    public function create(string $nome, ?int $cidadeId, bool $ativa, ?string $logo, ?int $usuarioId = null): Viacao
    {
        return DB::transaction(function () use ($nome, $cidadeId, $ativa, $logo, $usuarioId) {
            $viacao = Viacao::create([
                'nome' => $nome,
                'cidade_id' => $cidadeId,
                'ativa' => $ativa,
                'logo' => $logo,
            ]);

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Criado->value,
                'alteracoes' => [
                    'before' => null,
                    'after' => $viacao->only(['nome', 'ativa', 'logo', 'cidade_id']),
                ],
            ]);

            return $viacao;
        });
    }

    /**
     * Edita uma viação e registra o antes/depois no histórico.
     * Só salva no log os campos que efetivamente mudaram (diffRows).
     */
    public function update(Viacao $viacao, string $nome, ?int $cidadeId, bool $ativa, ?string $logo, ?int $usuarioId = null): Viacao
    {
        $oldLogo = $viacao->logo;

        DB::transaction(function () use ($viacao, $nome, $cidadeId, $ativa, $logo, $usuarioId) {
            // Captura o estado antes da edição, mas só os campos interessantes
            $before = $viacao->only(['nome', 'ativa', 'logo', 'cidade_id']);

            $viacao->update([
                'nome' => $nome,
                'cidade_id' => $cidadeId,
                'ativa' => $ativa,
                'logo' => $logo,
            ]);

            // Recarrega do banco pra pegar updated_at atualizado
            $viacao->refresh();
            $after = $viacao->only(['nome', 'ativa', 'logo']) + ['cidade_id' => $cidadeId];

            // Só salva os campos que realmente mudaram
            [$diffBefore, $diffAfter] = $this->diffRows($before, $after);

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Editado->value,
                'alteracoes' => ['before' => $diffBefore, 'after' => $diffAfter],
            ]);
        });

        /*
         * Side effects (arquivo) ficam FORA da transação.
         * Se o commit falhar, o arquivo não é deletado.
         * Pesquise "saga pattern", "two-phase commit".
         */
        if ($oldLogo !== null && $oldLogo !== $logo) {
            $this->uploadService->delete($oldLogo);
        }

        return $viacao;
    }

    /** Soft-deleta uma viação e registra no histórico. */
    public function delete(Viacao $viacao, ?int $usuarioId = null): void
    {
        $before = [
            'nome' => $viacao->nome,
            'cidade_id' => $viacao->cidade_id,
            'ativa' => $viacao->ativa,
            'logo' => $viacao->logo,
        ];

        DB::transaction(function () use ($viacao, $usuarioId, $before) {
            $viacao->delete(); // Com trait de SoftDeletes: seta deleted_at, não remove o registro. Pra remover MESMO, teria que usar forceDelete()

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Excluido->value,
                'alteracoes' => ['before' => $before, 'after' => null],
            ]);
        });

        // Agora que a viação não é mais removida de verdade melhor manter o logo pra não perder o vínculo ao restaurar.
        // Normalmente esse trecho seria removido do código, mas deixei só pra mostrar onde era chamado.
        /*if ($oldLogo !== null) {
            $this->uploadService->delete($oldLogo);
        }*/
    }

    /**
     * Restaura uma viação soft-deleted e registra no histórico.
     *
     * $viacao deve vir com withTrashed() (já buscado pelo controller).
     * restore() seta deleted_at = null, tornando o registro visível novamente.
     */
    public function restore(Viacao $viacao, ?int $usuarioId = null): void
    {
        DB::transaction(function () use ($viacao, $usuarioId) {
            $viacao->restore();

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Restaurado->value,
                'alteracoes' => [
                    'before' => null,
                    'after' => [
                        'nome' => $viacao->nome,
                        'cidade_id' => $viacao->cidade_id,
                        'ativa' => $viacao->ativa,
                        'logo' => $viacao->logo,
                    ],
                ],
            ]);
        });
    }

    /**
     * Compara antes e depois e retorna só os campos que mudaram.
     *
     * @return array{0: ?array, 1: ?array}
     */
    private function diffRows(?array $before, ?array $after): array
    {
        $skip = ['id', 'created_at', 'updated_at']; // não precisamos mostrar esses

        $allKeys = array_unique(array_merge(
            array_keys($before ?? []),
            array_keys($after ?? [])
        ));

        $diffBefore = [];
        $diffAfter = [];

        foreach ($allKeys as $key) {
            if (in_array($key, $skip, true)) {
                continue;
            }

            $valBefore = $before[$key] ?? null;
            $valAfter = $after[$key] ?? null;

            if ($valBefore !== $valAfter) {
                $diffBefore[$key] = $valBefore;
                $diffAfter[$key] = $valAfter;
            }
        }

        return [$diffBefore ?: null, $diffAfter ?: null];
    }
}
