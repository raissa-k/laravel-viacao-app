<?php

// Service de usuários: CRUD + registro no histórico unificado.
// Padrão idêntico ao ViacaoService: DB::transaction, before/after, diffRows.

namespace App\Services;

use App\DTOs\UsuarioFilterDTO;
use App\Enums\AcaoHistorico;
use App\Models\Usuario;
use App\Notifications\BemVindoNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioService
{
    public function all(UsuarioFilterDTO $filter = new UsuarioFilterDTO): LengthAwarePaginator
    {
        return Usuario::query()
            ->when($filter->deletado, fn ($q) => $q->onlyTrashed())
            ->when($filter->q !== '', function ($query) use ($filter) {
                $escaped = addcslashes($filter->q, '%_');
                $query->where(function ($q2) use ($escaped) {
                    $q2->where('nome', 'like', '%'.$escaped.'%')
                        ->orWhere('email', 'like', '%'.$escaped.'%');
                });
            })
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(string $nome, string $email, string $senha, ?int $atorId = null): Usuario
    {
        $novoUsuario = DB::transaction(function () use ($nome, $email, $senha, $atorId) {
            $usuario = Usuario::create([
                'nome' => $nome,
                'email' => $email,
                /*
                 * Hash::make() aplica bcrypt (ou o driver configurado em config/hashing.php).
                 * Nunca armazene senhas em texto puro.
                 * Pesquise "password hashing", "bcrypt cost factor", "Laravel Hash facade".
                 */
                'senha' => Hash::make($senha),
            ]);

            $usuario->historico()->create([
                'usuario_id' => $atorId,
                'acao' => AcaoHistorico::Criado->value,
                'alteracoes' => [
                    'before' => null,
                    'after' => $usuario->only(['nome', 'email']),
                ],
            ]);

            return $usuario;
        });

        $novoUsuario->notify(
            new BemVindoNotification(route('login'))
        );

        return $novoUsuario;
    }

    /** Atualiza nome/e-mail; só redefine a senha se $novaSenha for não-null. */
    public function update(Usuario $usuario, string $nome, string $email, ?string $novaSenha, ?int $atorId = null): Usuario
    {
        DB::transaction(function () use ($usuario, $nome, $email, $novaSenha, $atorId) {
            $before = $usuario->only(['nome', 'email']);

            $dados = ['nome' => $nome, 'email' => $email];
            if ($novaSenha !== null) {
                $dados['senha'] = Hash::make($novaSenha);
            }

            $usuario->update($dados);
            $usuario->refresh();

            // Não inclui senha no histórico: dado sensível não deve aparecer no log.
            $after = $usuario->only(['nome', 'email']);

            [$diffBefore, $diffAfter] = $this->diffRows($before, $after);

            $usuario->historico()->create([
                'usuario_id' => $atorId,
                'acao' => AcaoHistorico::Editado->value,
                'alteracoes' => ['before' => $diffBefore, 'after' => $diffAfter],
            ]);
        });

        return $usuario;
    }

    /** Soft-deleta o usuário e registra no histórico. */
    public function delete(Usuario $usuario, ?int $atorId = null): void
    {
        DB::transaction(function () use ($usuario, $atorId) {
            $before = $usuario->only(['nome', 'email']);

            $usuario->delete();

            $usuario->historico()->create([
                'usuario_id' => $atorId,
                'acao' => AcaoHistorico::Excluido->value,
                'alteracoes' => ['before' => $before, 'after' => null],
            ]);
        });
    }

    /** Restaura um usuário soft-deleted. */
    public function restore(Usuario $usuario, ?int $atorId = null): void
    {
        DB::transaction(function () use ($usuario, $atorId) {
            $usuario->restore();

            $usuario->historico()->create([
                'usuario_id' => $atorId,
                'acao' => AcaoHistorico::Restaurado->value,
                'alteracoes' => [
                    'before' => null,
                    'after' => $usuario->only(['nome', 'email']),
                ],
            ]);
        });
    }

    /** @return array{0: ?array, 1: ?array} */
    private function diffRows(?array $before, ?array $after): array
    {
        $allKeys = array_unique(array_merge(array_keys($before ?? []), array_keys($after ?? [])));
        $diffBefore = [];
        $diffAfter = [];

        foreach ($allKeys as $key) {
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
