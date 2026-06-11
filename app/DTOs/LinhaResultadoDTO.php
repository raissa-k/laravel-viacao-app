<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Categoria;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class LinhaResultadoDTO
{
    public function __construct(
        public int $id,
        public string $numero,
        public int $operadoraId,
        public string $operadoraNome,
        public string $duracao,
        public float $precoMinimo,
        public ?float $precoMaximo,
        public ?Categoria $categoria,
        public array $diasDaSemana,
    ) {
    }

    /** Constrói o DTO a partir do array bruto devolvido pela API. */
    public static function fromArray(array $data): self
    {
        $id            = (int) ($data['id'] ?? 0);
        $numero        = Str::title((string) ($data['numero'] ?? ''));
        $operadoraId   = (int) ($data['operadora_id'] ?? 0);
        $operadoraNome = Str::title((string) ($data['operadora_nome'] ?? ''));
        $duracao       = self::formatarDuracao((int) ($data['duracao_media_min'] ?? 0));
        $precoMinimo   = (float) ($data['preco_min'] ?? 0);

        $precoMaximo   = isset($data['preco_max'])
            ? (float) $data['preco_max']
            : null;

        $categoria     = Categoria::tryFrom((string) ($data['categoria'] ?? ''));
        $diasDaSemana  = self::normalizarDias($data['dias_semana'] ?? null);

        return new self(
            id: $id,
            numero: $numero,
            operadoraId: $operadoraId,
            operadoraNome: $operadoraNome,
            duracao: $duracao,
            precoMinimo: $precoMinimo,
            precoMaximo: $precoMaximo,
            categoria: $categoria,
            diasDaSemana: $diasDaSemana,
        );
    }

    /** Converte minutos para string legível. */
    private static function formatarDuracao(int $minutos): string
    {
        if ($minutos <= 0) {
            return '—';
        }

        $horas   = intdiv($minutos, 60);
        $minRest = $minutos % 60;

        if ($horas === 0) {
            return "{$minRest}m";
        }

        if ($minRest === 0) {
            return "{$horas}h";
        }

        return "{$horas}h {$minRest}m";
    }

    private static function normalizarDias(mixed $valor): array
    {
        $diasValidos = [
            'segunda',
            'terça',
            'quarta',
            'quinta',
            'sexta',
            'sábado',
            'domingo',
        ];

        if (!is_array($valor)) {
            return [];
        }

        return array_values(
            array_filter(
                $valor,
                fn ($dia) => is_string($dia)
                    && in_array($dia, $diasValidos, true)
            )
        );
    }

    /** Dados para teste. */
    public static function fake(): Collection
    {
        return collect([
            new self(
                id: 1,
                numero: '0101',
                operadoraId: 1,
                operadoraNome: 'Viação Itapemirim',
                duracao: '6h',
                precoMinimo: 89.90,
                precoMaximo: 149.90,
                categoria: Categoria::Executivo,
                diasDaSemana: [
                    'segunda',
                    'terça',
                    'quarta',
                    'quinta',
                    'sexta',
                    'sábado',
                    'domingo',
                ],
            ),
            new self(
                id: 2,
                numero: '0202',
                operadoraId: 2,
                operadoraNome: 'Cometa',
                duracao: '8h 11m',
                precoMinimo: 45.00,
                precoMaximo: 75.00,
                categoria: Categoria::Convencional,
                diasDaSemana: [
                    'segunda',
                    'terça',
                    'quarta',
                    'quinta',
                    'sexta',
                ],
            ),
            new self(
                id: 3,
                numero: '0303',
                operadoraId: 3,
                operadoraNome: 'Catarinense',
                duracao: '45m',
                precoMinimo: 120.00,
                precoMaximo: null,
                categoria: Categoria::Leito,
                diasDaSemana: [],
            ),
        ]);
    }
}
