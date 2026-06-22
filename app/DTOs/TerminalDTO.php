<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Support\Str;

final readonly class TerminalDTO
{
    public function __construct(
        public int    $id,
        public string $nome,
        public string $endereco,
        public string $telefone,
        public string $funcionamento,
        public array  $servicos,
        public string $cidade,
        public string $uf,
    ) {
    }


    /** Constrói o DTO a partir do array bruto devolvido pela API. */
    public static function fromArray(array $dados, ?\App\Models\Cidade $cidadeLocal = null): self
    {
        $id             = (int) ($dados['id'] ?? 0);
        $nome           = Str::title((string) ($dados['nome'] ?? '')); //teste 1
        $endereco       = Str::title((string) ($dados['endereco'] ?? ''));//teste 2
        $telefone       = (string) ($dados['telefone'] ?? '');
        $funcionamento  = (string) ($dados['funcionamento'] ?? '');
        $servicos       = (array) ($dados['servicos'] ?? []);

        $cidade         = $cidadeLocal?->nome ?? Str::title((string) ($dados['cidade'] ?? 'Sem Cidade')); //teste 3,se ele esta convertendo corretamente e se o fallback esta acontecendo
        $uf             = $cidadeLocal?->uf   ?? strtoupper((string) ($dados['uf'] ?? '--'));//teste 4

        return new self(
            id: $id,
            nome: $nome,
            endereco: $endereco,
            telefone: $telefone,
            funcionamento: $funcionamento,
            servicos: $servicos,
            cidade: $cidade,
            uf: $uf,
        );
    }
}
