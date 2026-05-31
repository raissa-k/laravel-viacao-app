<?php

// DTO de filtros pro histórico de alterações.
// Mesmo raciocínio do ViacaoFilterDTO: normaliza e tipa parâmetros GET sem redirecionar o usuário em caso de valor inválido.

namespace App\DTOs;

use App\DTOs\Contracts\FilterDTO;
use App\Enums\AcaoHistorico;
use Carbon\Carbon;
use Illuminate\Http\Request;

final readonly class HistoricoFilterDTO implements FilterDTO
{
    public function __construct(
        /*
         * Antes: public ?string $acao = null
         * Agora: public ?AcaoHistorico $acao = null
         *
         * O que muda na prática?
         * Com ?AcaoHistorico, o PHP garante que $acao só pode ser um dos três cases do enum ou null.
         * É impossível ter um valor inválido aqui, o tipo não deixa.
         * Pesquise "type safety".
         */
        public ?AcaoHistorico $acao = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public string $q = '',
        public ?int $viacaoId = null,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $viacaoIdRaw = $request->input('viacao_id');

        return new self(
            /*
             * tryFrom() recebe uma string e devolve o case correspondente, ou null se não encontrar.
             * A diferença: in_array() é uma verificação manual que você precisa lembrar de fazer.
             * tryFrom() é automático porque faz parte do contrato do enum backed.
             */
            acao: AcaoHistorico::tryFrom((string) $request->input('acao', '')),
            dateFrom: self::parseDate($request->input('date_from')),
            dateTo: self::parseDate($request->input('date_to')),
            q: trim((string) $request->input('q', '')),
            viacaoId: is_numeric($viacaoIdRaw) && (int) $viacaoIdRaw > 0 ? (int) $viacaoIdRaw : null,
        );
    }

    /*
     * Valida e normaliza uma string de data no formato Y-m-d.
     * Retorna a string original se válida, null se inválida ou vazia.
     */
    private static function parseDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            // Carbon::createFromFormat() retorna Carbon instance (extends DateTime)
            // Benefícios do Carbon:
            // 1. Fácil de chain e entender o que cada coisa faz: $date->format('Y-m-d')->addDays(7)->toDateString()
            // 2. Comparações: $date->isBetween(), $date->isPast(), $date->isToday() etc.
            // 3. Legível: $date->diffForHumans() => "1 hour ago" (com locale)
            // 4. Gestão facilitada de timezones
            // 5. Manipulação: $date->addMonths(3)->subDays(5) é natural
            // 6. Localização: ->translatedFormat() respeita locale da app ("Saturday, Jan 10" vs "sábado, 10 de janeiro")
            $d = Carbon::createFromFormat('Y-m-d', $value);

            // Detecta normalização: se a data foi "ajustada" pelo PHP, a formatação não bate
            // Exemplo: '2025-02-30' vira 2 de março, e ao reformatar fica '2025-03-02'
            return ($d->format('Y-m-d') === $value) ? $value : null;
        } catch (\Throwable $e) {
            // Carbon lança exception em caso de formato inválido (ex: "not-a-date"), devolvemos null para manter o contrato da função
            return null;
        }
    }
}
