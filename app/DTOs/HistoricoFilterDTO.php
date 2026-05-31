<?php

// DTO de filtros pro histórico de alterações.
// Mesmo padrão dos outros DTOs: normaliza e tipa parâmetros GET.
//
// Campo 'entidade' controla qual aba está ativa (viacoes ou usuarios).
// Isso separa o histórico de cada entidade em abas distintas na view, sem precisar de duas rotas ou dois controllers.

namespace App\DTOs;

use App\DTOs\Contracts\FilterDTO;
use App\Enums\AcaoHistorico;
use App\Enums\EntidadeHistorico;
use Carbon\Carbon;
use Illuminate\Http\Request;

final readonly class HistoricoFilterDTO implements FilterDTO
{
    public function __construct(
        /*
         * entidade: aba ativa da listagem.
         * EntidadeHistorico garante que só valores válidos chegam ao service:
         * tryFrom('foo') -> null -> fallback para Viacao. Sem enum, qualquer string passaria.
         */
        public EntidadeHistorico $entidade = EntidadeHistorico::Viacao,
        public ?AcaoHistorico $acao = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public string $q = '',
    ) {}

    public static function fromRequest(Request $request): static
    {
        return new self(
            /*
             * tryFrom() devolve null para valores inválidos (ex: 'viacoes', 'Viacao').
             * O ?? garante o fallback para a aba padrão sem exception.
             * Mesmo padrão do AcaoHistorico no mesmo DTO.
             */
            entidade: EntidadeHistorico::tryFrom((string) $request->input('entidade', '')) ?? EntidadeHistorico::Viacao,
            acao: AcaoHistorico::tryFrom((string) $request->input('acao', '')),
            dateFrom: self::parseDate($request->input('date_from')),
            dateTo: self::parseDate($request->input('date_to')),
            q: trim((string) $request->input('q', '')),
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
