<?php

// FormRequest para criação e edição de viações via API REST.

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cidade;

/*
 * Por que um FormRequest separado para a API, se ViacaoRequest já existe?
 *
 * ViacaoRequest tem a regra 'logo' (upload de imagem), que a API não suporta.
 * Dois FormRequests pequenos e explícitos são mais fáceis de entender e manter do que uma hierarquia de herança com sobrescritas parciais.
 */

class ViacaoApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        // As rotas já são protegidas pelo middleware auth:sanctum.
        // Se chegou aqui, o token foi validado e o usuário está autenticado.
        return true;
    }

    public function rules(): array
    {
        $rules = static::coreRules();

        // PUT (atualização): cidade e nome são opcionais pra permitir atualizações parciais
        if ($this->isMethod('PUT')) {
            $rules['cidade_id'] = ['sometimes', 'nullable', 'integer', 'exists:cidades,id'];
            $rules['nome'] = ['sometimes', 'string', 'max:255'];
        }

        return $rules;
    }

    /*
     * Regras separadas em static pra que o comando Artisan ImportViacoes (que também não processa logo)
     * possa reutilizar via Validator::make() sem ter que instanciar um FormRequest (que requer HTTP context).
     * Fonte única de verdade: mudar aqui aplica em HTTP e CLI ao mesmo tempo.
     *
     * Por que não se chama 'rules()' ou 'validationRules()'?
     * FormRequest já define esses nomes, e não permite redeclarar como estáticos numa subclasse.
     * Poderíamos colocar diretamente dentro de 'rules()' ali em cima? Sim, mas aí ImportViacoes não conseguiria acessar.
     */
    public static function coreRules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cidade_id' => ['sometimes', 'nullable', 'integer', 'exists:cidades,id'],            // 'sometimes': só valida se o campo estiver presente.
            // Útil pra clientes que omitem 'ativa' na requisição por qualquer motivo que seja.
            'ativa' => ['sometimes', 'boolean'],
        ];
    }

    /*
     * prepareForValidation: normaliza antes de validar.
     * Sem isso, " Catarinense " (com espaços) passaria na validação e seria salvo com espaços.
     * Mesma lógica do ViacaoRequest web, clientes de API também enviam strings com espaços acidentais.
     *
     * Por que 'ativa' só entra no merge se estiver presente no request?
     * Formulários web: checkbox desmarcado não envia o campo.
     * Então, ausente = false é o comportamento certo. ViacaoRequest sempre inclui 'ativa' no merge, porque ausente significa "desmarcado".
     *
     * APIs REST: ausente significa "não alterar".
     * Um cliente que manda só {'nome': 'Nova'} quer atualizar o nome e manter todos os outros campos intactos, não setar 'ativa' como true.
     * Se incluíssemos 'ativa' no merge incondicionalmente, ausente viraria true (default do boolean()), e o update sempre sobrescreveria o campo
     * Aqui, só normalizamos 'ativa' se o cliente enviou explicitamente o campo.
     *
     * Pesquise "REST PATCH vs PUT semantics", "partial update API design".
     */
    protected function prepareForValidation(): void
    {
        $merge = [
            'nome' => trim((string) $this->input('nome', '')),
        ];

        // Se veio cidade_id direto, usa ele. Se veio cidade (nome), resolve para ID.
        if ($this->has('cidade_id')) {
            $merge['cidade_id'] = $this->input('cidade_id');
        } elseif ($this->has('cidade')) {
            $nomeCidade = trim((string) $this->input('cidade', ''));
            $cidade = Cidade::where('nome', $nomeCidade)->first();
            $merge['cidade_id'] = $cidade?->id; // null se não encontrar
        }

        if ($this->has('ativa')) {
            $merge['ativa'] = $this->boolean('ativa');
        }

        $this->merge($merge);
    }
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 255 caracteres.',
            'cidade_id.integer' => 'A cidade é inválida.',
            'cidade_id.exists' => 'A cidade selecionada não existe.',
        ];
    }
}
