<?php

declare(strict_types=1);

// FormRequest de viação: substitui o ViacaoValidator do PHP puro.
// FormRequest é resolvido automaticamente antes do controller executar.
// Se a validação falhar, o Laravel faz o redirect com erros e old input automaticamente.
// Pesquise "Laravel Form Request", "automatic validation redirect".

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No PHP puro, a autorização era feita pelo AuthMiddleware antes do controller.
        // Aqui retornamos true porque as rotas já são protegidas pelo middleware 'auth'.
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'      => ['required', 'string', 'max:255'],
            'cidade_id' => ['nullable', 'integer', 'exists:cidades,id'],
            // 'sometimes': só valida se o campo estiver presente (no edit, o checkbox pode estar ausente).
            'ativa'     => ['sometimes', 'boolean'],
            // 'image': valida MIME pelo conteúdo do arquivo, não pela extensão.
            // 'max:2048': tamanho máximo em KB (2048 KB = 2 MB).
            'logo'      => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'site'      => ['nullable', 'string', 'url', 'max:255'],
        ];
    }

    /*
     * prepareForValidation: normaliza os campos antes da validação.
     * trim em nome/cidade: remove espaços acidentais nas pontas (ex: " Catarinense " -> "Catarinense").
     * boolean('ativa'): lida com checkbox desmarcado (campo ausente vira false) e com "1"/"on"/true.
     */
    protected function prepareForValidation(): void
    {
        $siteInput = trim((string) $this->input('site', ''));
        $this->merge([
            'nome'  => trim((string) $this->input('nome', '')),
            'ativa' => $this->boolean('ativa'),
            'site'  => $siteInput !== '' ? $siteInput : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'nome.required'     => 'O nome é obrigatório.',
            'nome.max'          => 'O nome deve ter no máximo 255 caracteres.',
            'cidade_id.integer' => 'A cidade é inválida.',
            'cidade_id.exists'  => 'A cidade selecionada não existe.',
            'logo.image'        => 'O logo deve ser uma imagem.',
            'logo.mimes'        => 'O logo deve ser JPG, PNG ou WEBP.',
            'logo.max'          => 'O logo deve ter no máximo 2 MB.',
            'site.url'          => 'O site deve ser uma URL.',
        ];
    }
}
