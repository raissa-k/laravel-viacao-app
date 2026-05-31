<?php

// Service de viações: regra de negócio de criação, edição, exclusão e registro de histórico.
// PHP puro: PDO + beginTransaction() + prepare() + execute()
// Laravel:  Eloquent + DB::transaction() + Model::create() + $model->update()

namespace App\Services;

use App\DTOs\ViacaoFilterDTO;
use App\Enums\AcaoHistorico;
use App\Models\Viacao;
use App\Models\ViacaoHistorico;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ViacaoService
{
    public function __construct(
        private readonly UploadService $uploadService,
    ) {}

    /**
     * Retorna viações com filtros encapsulados no DTO. O DTO já normalizou e tipou os valores antes de chegar no service.
     *
     * NOTA EDUCACIONAL sobre Eager Loading:
     * Aqui NÃO usamos with() para carregar relacionamentos (ex: historico).
     * Por quê?
     * - A view admin/viacoes/index.blade.php SÓ mostra campos da viação (nome, cidade, ativa)
     * - O histórico é acessado via rota separada (/admin/historico ou /admin/viacoes/{id}/historico)
     * - Se no futuro necessitar do histórico aqui, ENTÃO adicionar with(['historico'])
     * - Até lá deixar sem with() economiza queries desnecessárias
     *
     * Regra: eager-load APENAS os relacionamentos que você SABE que vai usar na view/response.
     * Compare com HistoricoService que SEMPRE usa with(['viacao', 'usuario']) porque a
     * view admin/historico/index.blade.php sempre acessa $h->viacao->nome e $h->usuario->nome.
     *
     * Pesquise: "lazy vs eager loading trade-offs", "premature optimization".
     */
    public function all(ViacaoFilterDTO $filter = new ViacaoFilterDTO): Collection
    {
        return Viacao::query()
            ->when($filter->q !== '', function ($query) use ($filter) {
                // addcslashes escapa % e _ que o MySQL interpreta como wildcards no LIKE.
                // Sem isso, "100%" no campo de busca viraria "qualquer coisa começando com 100".
                // Pesquise "SQL LIKE wildcards", "ESCAPE clause".
                $escaped = addcslashes($filter->q, '%_');
                $query->where(function ($q2) use ($escaped) {
                    $q2->where('nome', 'like', '%'.$escaped.'%')
                        ->orWhere('cidade', 'like', '%'.$escaped.'%');
                });
            })
            ->when($filter->ativa !== null, fn ($query) => $query->where('ativa', $filter->ativa))
            ->orderByDesc('id')
            ->get();
    }

    /** Retorna só as viações ativas. Usada na home pública. */
    public function active(): Collection
    {
        return Viacao::query()->where('ativa', true)->orderByDesc('id')->get();
    }

    /** Busca uma viação pelo ID. Retorna null se não encontrar. */
    public function find(int $id): ?Viacao
    {
        return Viacao::find($id);
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
    public function create(string $nome, string $cidade, bool $ativa, ?string $logo, ?int $usuarioId = null): Viacao
    {
        return DB::transaction(function () use ($nome, $cidade, $ativa, $logo, $usuarioId) {
            $viacao = Viacao::create([
                'nome' => $nome,
                'cidade' => $cidade,
                'ativa' => $ativa,
                'logo' => $logo,
            ]);

            // Histórico: before = null (não existia), after = estado inicial
            ViacaoHistorico::create([
                'viacao_id' => $viacao->id,
                'usuario_id' => $usuarioId,
                /*
                 * AcaoHistorico::Criado->value, string derivada do enum.
                 * Não há como salvar um valor inválido ("Criad" ao invés de "Criado") acidentalmente.
                 * Pesquise "Backed enums PHP 8.1", "type safety".
                 */
                'acao' => AcaoHistorico::Criado->value,
                'alteracoes' => [
                    'before' => null,
                    'after' => $viacao->only(['nome', 'cidade', 'ativa', 'logo']), // não precisamos mostrar ID, data de criação, etc
                ],
            ]);

            return $viacao;
        });
    }

    /**
     * Edita uma viação e registra o antes/depois no histórico.
     *
     * $viacao->only([...]) retorna valores já convertidos pelo cast (bool, não "1"/"0")
     * O diff funciona igual, a diferença é o tipo dos valores armazenados no JSON.
     *
     * Transações: DB::transaction() com rollback automático
     * - Se qualquer Eloquent::create() ou update() falhar dentro da closure, a exception é propagada
     * - O rollback é automático, não precisa try/catch explícito
     * - Pesquise "Laravel DB::transaction", "ACID properties", "database transactions".
     */
    public function update(Viacao $viacao, string $nome, string $cidade, bool $ativa, ?string $logo, ?int $usuarioId = null): Viacao
    {
        $oldLogo = $viacao->logo;

        DB::transaction(function () use ($viacao, $nome, $cidade, $ativa, $logo, $usuarioId) {
            // Captura o estado antes da edição, mas só os campos interessantes
            $before = $viacao->only(['nome', 'cidade', 'ativa', 'logo']);

            $viacao->update([
                'nome' => $nome,
                'cidade' => $cidade,
                'ativa' => $ativa,
                'logo' => $logo,
            ]);

            // Recarrega do banco pra pegar updated_at atualizado
            $viacao->refresh();
            $after = $viacao->only(['nome', 'cidade', 'ativa', 'logo']);

            // Só salva os campos que realmente mudaram
            [$diffBefore, $diffAfter] = $this->diffRows($before, $after);

            ViacaoHistorico::create([
                'viacao_id' => $viacao->id,
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Editado->value,
                'alteracoes' => ['before' => $diffBefore, 'after' => $diffAfter],
            ]);
        });

        /*
         * Remoção do logo antigo DEPOIS do commit (fora da transação).
         * Pattern importante: side effects (arquivo, cache, webhooks) devem ficar fora da transação.
         * Se deletar dentro da closure e o commit falhar, o arquivo sumiu com o rollback. A viação não foi alterada mas ficou sem logo.
         * Fora do DB::transaction(), só chegamos aqui se o commit foi bem-sucedido.
         * Pesquise "distributed transactions", "two-phase commit", "saga pattern".
         */
        if ($oldLogo !== null && $oldLogo !== $logo) {
            $this->uploadService->delete($oldLogo);
        }

        return $viacao;
    }

    /** Exclui uma viação e registra no histórico. */
    public function delete(Viacao $viacao, ?int $usuarioId = null): void
    {
        $oldLogo = $viacao->logo;
        $id = $viacao->id;
        $before = $viacao->only(['nome', 'cidade', 'ativa', 'logo']);

        DB::transaction(function () use ($viacao, $id, $usuarioId, $before) {
            $viacao->delete();

            // after = null porque a viação não existe mais
            ViacaoHistorico::create([
                'viacao_id' => $id,
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Excluido->value,
                'alteracoes' => ['before' => $before, 'after' => null],
            ]);
        });

        if ($oldLogo !== null) {
            $this->uploadService->delete($oldLogo);
        }
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
