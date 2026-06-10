<?php

namespace App\Services;

use App\Models\Cidade;
use Ramsey\Collection\Collection;

class CidadeService
{
    public function all(bool $ordenarPorNome = true): Collection
    {
        $query = cidade::query();
        if ($ordenarPorNome) {
            $query->orderBy('nome');
        }
        return $query->get();
    }
    public function find(int $id): ?Cidade {
        return Cidade::find($id);
    }
}
