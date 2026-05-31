<?php

// DTO de filtros para listagem de usuários.

namespace App\DTOs;

use App\DTOs\Contracts\FilterDTO;
use Illuminate\Http\Request;

/*
 * Mesmo que o único filtro seja 'q', ter um DTO dedicado mantém o padrão uniforme.
 * Todos os controllers de listagem recebem um DTO, nunca $request->get() direto.
 * Quando um novo filtro (ex: 'role', 'ativo') for adicionado, há um lugar óbvio pra colocar.
 */

final readonly class UsuarioFilterDTO implements FilterDTO
{
    public function __construct(
        public string $q = '',
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            q: trim((string) $request->input('q', '')),
        );
    }
}
