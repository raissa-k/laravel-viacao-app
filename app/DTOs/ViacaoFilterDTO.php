<?php

// DTO de filtros para listagem de viações.
// No PHP puro, os filtros eram lidos diretamente de $_GET no controller.
// Aqui, o DTO encapsula a leitura, normalização e tipagem dos parâmetros de filtro.
//
// Por que DTO e não FormRequest?
// FormRequest é pra POST/PUT com redirecionamento em caso de erro.
// Inadequado pra GET onde valores inválidos podem ser ignorados silenciosamente, não rejeitados.
// O DTO normaliza a entrada e garante tipos corretos pro service.
// Pesquise "Data Transfer Object", "readonly class PHP".

namespace App\DTOs;

use App\DTOs\Contracts\FilterDTO;
use Illuminate\Http\Request;

final readonly class ViacaoFilterDTO implements FilterDTO
{
    public function __construct(
        public string $q = '',
        public ?bool $ativa = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $q = trim((string) $request->input('q', ''));

        // Converte '1'/'0'/'' para true/false/null sem lançar erro em valores inválidos.
        $ativaRaw = $request->input('ativa');
        $ativa = match (true) {
            $ativaRaw === '1' || $ativaRaw === 1 => true,
            $ativaRaw === '0' || $ativaRaw === 0 => false,
            default => null,
        };

        return new self(q: $q, ativa: $ativa);
    }
}
