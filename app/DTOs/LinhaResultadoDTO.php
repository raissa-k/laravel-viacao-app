<?php

declare(strict_types=1);

// DTO que representa uma linha de ônibus retornada pela API externa de transporte.
// recebe o array bruto da API e normaliza os dados antes de chegarem às views.

namespace App\DTOs;

use App\Enums\Categoria;

final readonly class LinhaResultadoDTO
{
    public function __construct(
        public int      $id,
        public string   $numero,
        public int      $operadoraId,
        public string   $operadoraNome,
        public string   $duracao,
        public float    $precoMinimo,
        public float    $precoMaximo,
        public ?Categoria $categoria,
        public array    $diasDaSemana,
    ) {}

    /** constrói o DTO a partir do array bruto devolvido pela API. */
    public static function fromArray(array $data): self
    {
        return new self(
            id:            (int) $data['id'],
            numero:        mb_convert_case((string) ($data['numero'] ?? ''), MB_CASE_TITLE, 'UTF-8'),
            operadoraId:   (int) ($data['operadora_id'] ?? 0),
            operadoraNome: mb_convert_case((string) ($data['operadora_nome'] ?? ''), MB_CASE_TITLE, 'UTF-8'),
            duracao:       self::formatarDuracao((int) ($data['duracao_media_min'] ?? 0)),
            precoMinimo:   (float) ($data['preco_min'] ?? 0),
            precoMaximo:   (float) ($data['preco_max'] ?? 0),
            categoria:     Categoria::tryFrom((string) ($data['categoria'] ?? '')),
            diasDaSemana: self::normalizarDias($data['dias_semana'] ?? null),
        );
    }

    /** converte minutos para string legível.
        ex: 360 -> "6h" | 491 -> "8h 11m" | 45 -> "45m" */
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
        $diasValidos = ['segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado', 'domingo'];

        if (!is_array($valor)) {
            return [];
        }

        return array_values(
            array_filter(
                $valor,
                fn($dia) => is_string($dia) && in_array($dia, $diasValidos, strict: true)
            )
        );
    }

    /** Dados para teste */
    public static function fake(): array
    {
        return [
            new self(
                id:            1,
                numero:        '0101',
                operadoraId:   1,
                operadoraNome: 'Viação Itapemirim',
                duracao:       '6h',
                precoMinimo:   89.90,
                precoMaximo:   149.90,
                categoria:     Categoria::Executivo,
                diasDaSemana:  ['segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado', 'domingo'],
            ),
            new self(
                id:            2,
                numero:        '0202',
                operadoraId:   2,
                operadoraNome: 'Cometa',
                duracao:       '8h 11m',
                precoMinimo:   45.00,
                precoMaximo:   75.00,
                categoria:     Categoria::Convencional,
                diasDaSemana:  ['segunda', 'terça', 'quarta', 'quinta', 'sexta'],
            ),
            new self(
                id:            3,
                numero:        '0303',
                operadoraId:   3,
                operadoraNome: 'Catarinense',
                duracao:       '45m',
                precoMinimo:   120.00,
                precoMaximo:   200.00,
                categoria:     Categoria::Leito,
                diasDaSemana:  [],
            ),
        ];
    }
}
