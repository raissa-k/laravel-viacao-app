<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Viacao;

class ViacaoPolicy
{
    public function delete(Usuario $usuario, Viacao $viacao): bool
    {
        return $viacao->ativa === false;
    }
}
