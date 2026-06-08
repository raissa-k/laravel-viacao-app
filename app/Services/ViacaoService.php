<?php

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

    public function all(ViacaoFilterDTO $filter = new ViacaoFilterDTO): LengthAwarePaginator
    {
        return Viacao::query()
            ->when($filter->deletado, fn ($q) => $q->onlyTrashed())
            ->when($filter->q !== '', function ($query) use ($filter) {
                $escaped = addcslashes($filter->q, '%_');
                $query->where(function ($q2) use ($escaped) {
                    $q2->where('nome', 'like', '%'.$escaped.'%')
                        ->orWhere('cidade', 'like', '%'.$escaped.'%');
                });
            })
            ->when($filter->ativa !== null, fn ($query) => $query->where('ativa', $filter->ativa))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function active(): Collection
    {
        return Viacao::query()->where('ativa', true)->orderByDesc('id')->get();
    }

    public function find(int $id): ?Viacao
    {
        return Viacao::find($id);
    }

    public function create(string $nome, string $cidade, bool $ativa, ?string $logo, ?string $site = null, ?int $usuarioId = null): Viacao
    {
        return DB::transaction(function () use ($nome, $cidade, $ativa, $logo, $usuarioId, $site) {
            $viacao = Viacao::create([
                'nome' => $nome,
                'cidade' => $cidade,
                'ativa' => $ativa,
                'logo' => $logo,
                'site' => $site,
            ]);

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Criado->value,
                'alteracoes' => [
                    'before' => null,
                    'after' => $viacao->only(['nome', 'cidade', 'ativa', 'logo', 'site']),
                ],
            ]);

            return $viacao;
        });
    }

    public function update(Viacao $viacao, string $nome, string $cidade, bool $ativa, ?string $logo, ?string $site = null, ?int $usuarioId = null): Viacao
    {
        $oldLogo = $viacao->logo;

        DB::transaction(function () use ($viacao, $nome, $cidade, $ativa, $logo, $usuarioId, $site) {
            $before = $viacao->only(['nome', 'cidade', 'ativa', 'logo', 'site']);

            $viacao->update([
                'nome' => $nome,
                'cidade' => $cidade,
                'ativa' => $ativa,
                'logo' => $logo,
                'site' => $site,
            ]);

            $viacao->refresh();
            $after = $viacao->only(['nome', 'cidade', 'ativa', 'logo', 'site']);

            [$diffBefore, $diffAfter] = $this->diffRows($before, $after);

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Editado->value,
                'alteracoes' => ['before' => $diffBefore, 'after' => $diffAfter],
            ]);
        });

        if ($oldLogo !== null && $oldLogo !== $logo) {
            $this->uploadService->delete($oldLogo);
        }

        return $viacao;
    }

    public function delete(Viacao $viacao, ?int $usuarioId = null): void
    {
        $before = $viacao->only(['nome', 'cidade', 'ativa', 'logo']);

        DB::transaction(function () use ($viacao, $usuarioId, $before) {
            $viacao->delete();

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Excluido->value,
                'alteracoes' => ['before' => $before, 'after' => null],
            ]);
        });
    }

    public function restore(Viacao $viacao, ?int $usuarioId = null): void
    {
        DB::transaction(function () use ($viacao, $usuarioId) {
            $viacao->restore();

            $viacao->historico()->create([
                'usuario_id' => $usuarioId,
                'acao' => AcaoHistorico::Restaurado->value,
                'alteracoes' => [
                    'before' => null,
                    'after' => $viacao->only(['nome', 'cidade', 'ativa', 'logo', 'site']),
                ],
            ]);
        });
    }

    private function diffRows(?array $before, ?array $after): array
    {
        $skip = ['id', 'created_at', 'updated_at'];

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
