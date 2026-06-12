<?php

declare(strict_types=1);

// Enum das ações possíveis no histórico de viações.

namespace App\Enums;

/*
 * O que é um enum?
 * Um enum (enumeração) é um tipo que representa um conjunto fixo de valores possíveis.
 * Antes dos enums (PHP < 8.1), usávamos constantes ou strings avulsas:
 *
 * Antes: strings sem garantia de valor válido
 * * ViacaoHistorico::create(['acao' => 'Criado', ...]);
 * * ViacaoHistorico::create(['acao' => 'criado', ...]); // casing errado
 * * ViacaoHistorico::create(['acao' => 'Deleteixon', ...]); // valor inválido
 *
 * Com enum: o PHP impede valores inválidos em tempo de execução
 * ViacaoHistorico::create(['acao' => AcaoHistorico::Criado->value, ...]);
 * AcaoHistorico::Deleteixon -> erro imediato: case não existe
 *
 * Por que "backed enum" (enum com : string)?
 * Um backed enum tem um valor associado a cada case (aqui, string).
 * Isso permite:
 * - Salvar no banco: AcaoHistorico::Criado->value == 'Criado'
 * - Recuperar do input: AcaoHistorico::tryFrom('Criado') == AcaoHistorico::Criado
 * - Comparar sem string: $filter->acao === AcaoHistorico::Criado
 *
 * Pesquise "backed enum PHP", "pure enum vs backed enum".
 */

enum AcaoHistorico: string
{
    case Criado     = 'Criado';
    case Editado    = 'Editado';
    case Excluido   = 'Excluido';
    case Restaurado = 'Restaurado';

    public function tipoBadge(): string
    {
        return match ($this) {
            self::Criado     => 'success',
            self::Editado    => 'warning',
            self::Excluido   => 'error',
            self::Restaurado => 'info',
        };
    }

    /*
     * tryFrom() vs from():
     * from('Criado')    -> AcaoHistorico::Criado  (ou erro se não existir)
     * tryFrom('Criado') -> AcaoHistorico::Criado  (ou null se não existir, sem exception)
     *
     * Para input do usuário (GET, POST) é legal usar tryFrom(),
     * o usuário pode enviar qualquer string e tryFrom() trata o caso inválido devolvendo null, enquanto from() lançaria uma exception.
     *
     * Exemplo de uso no DTO:
     * AcaoHistorico::tryFrom($request->input('acao', ''))
     * 'Criado'  -> AcaoHistorico::Criado
     * 'foo'     -> null  (sem filtro)
     * ''        -> null  (sem filtro)
     *
     * Note que tryFrom() já é herdado automaticamente de todos os backed enums.
     */
}
