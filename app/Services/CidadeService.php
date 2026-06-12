<?php

namespace App\Services;

use App\Models\Cidade;
use Illuminate\Database\Eloquent\Collection;


class CidadeService
{
    public function all(bool $ordenarPorNome = true): Collection
    {
        $query = Cidade::query();
        if ($ordenarPorNome) {
            $query->orderBy('nome');
        }
        return $query->get();
    }
    public function find(int $id): ?Cidade {
        return Cidade::find($id);
    }
}
