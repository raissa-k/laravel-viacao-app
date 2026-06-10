<?php

declare(strict_types=1);

// API Resource: define o shape exato do JSON retornado pela API.
// No PHP puro, o shape era determinado pelo que cada um fazia com json_encode($model->toArray()).
// No Laravel: Resource oferece uma camada de controle e você decide exatamente quais campos incluir.
//
// Benefícios educacionais:
// 1. Separação: model (persistência) vs resource (apresentação no JSON)
// 2. Segurança: não expõe acidentalmente colunas internas (ex: senha, deleted_at, etc.)
// 3. Evolução: se o schema mudar, o JSON fica igual (backwards compatibility)
//
// Pesquise "Laravel Resource", "API resource transformation", "API versioning".

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViacaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nome'       => $this->nome,
            'cidade'     => $this->cidade?->nome,
            'ativa'      => $this->ativa,
            // Logo será null ou uma string filename, geralmente não incluímos o path em APIs
            // Por enquanto, só retornamos o filename e não o caminho para mostrar o arquivo
            'logo'       => $this->logo,
            // CARBON NO LARAVEL:
            // $this->created_at e $this->updated_at são instâncias de Carbon (extends DateTime).
            // Laravel converte automaticamente campos de data do banco para Carbon.

            // Por que Carbon em vez de DateTime nativo?
            // 1. Fácil de chain e entender o que cada coisa faz: $date->format('Y-m-d')->addDays(7)->toDateString()
            // 2. Comparações: $date->isBetween(), $date->isPast(), $date->isToday() etc.
            // 3. Legível: $date->diffForHumans() => "1 hour ago" (com locale)
            // 4. Gestão facilitada de timezones
            // 5. Manipulação: $date->addMonths(3)->subDays(5) é natural
            // 6. Localização: ->translatedFormat() respeita locale da app ("Saturday, Jan 10" vs "sábado, 10 de janeiro")
            //
            // Nesta API, convertemos pra ISO8601 (padrão web):
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
