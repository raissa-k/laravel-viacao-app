<?php

// Interface comum pra todos os DTOs de filtro GET.

namespace App\DTOs\Contracts;

use Illuminate\Http\Request;

/*
 * O que é uma interface?
 * Uma interface define um CONTRATO: uma lista de methods que qualquer classe que a implemente DEVE ter.
 * A interface não implementa nada, só diz "quem usar isso precisa ter esses methods".
 *
 * Por que criar uma interface pros DTOs de filtro?
 * ViacaoFilterDTO e HistoricoFilterDTO têm o mesmo padrão com fromRequest().
 * Sem interface, cada DTO é independente e nada garante que o padrão continue.
 * Com interface, qualquer novo DTO de filtro que implemente FilterDTO é obrigado a ter fromRequest().
 *
 * Vantagem prática: você pode tipar parâmetros como FilterDTO:
 * function processarFiltro(FilterDTO $dto): void { ... }
 * Isso funciona com ViacaoFilterDTO, HistoricoFilterDTO, ou qualquer outro que implemente a interface.
 *
 * Pesquise "interface PHP", "dependency inversion principle".
 */

interface FilterDTO
{
    /*
     * Por que static em vez de self?
     * self sempre se refere à interface ou classe onde o method está definido.
     * static se refere à classe que está sendo chamada.
     *
     * ViacaoFilterDTO::fromRequest($request)    -> retorna ViacaoFilterDTO (não FilterDTO)
     * HistoricoFilterDTO::fromRequest($request) -> retorna HistoricoFilterDTO
     *
     * Com self, o tipo de retorno seria sempre FilterDTO, o PHP não saberia que ViacaoFilterDTO::fromRequest() devolve especificamente um ViacaoFilterDTO.
     * Com static, o PHP entende o tipo correto em cada subclasse.
     *
     * Pesquise "late static binding PHP", "static vs self return type".
     */
    public static function fromRequest(Request $request): static;
}
