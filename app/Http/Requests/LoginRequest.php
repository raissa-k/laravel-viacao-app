<?php

declare(strict_types=1);

// LoginRequest intercepta o request ANTES de chegar ao controller.
// Se a validação falhar o framework redireciona de volta com erros automaticamente.
// Se passar o controller recebe um $request já validado, sem try/catch.
//
// Pesquise "Laravel Form Request", "validation before controller".

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /*
     * prepareForValidation: normaliza os campos ANTES de aplicar as rules.
     *
     * Por que trim() no email mas NÃO na senha?
     * Email: espaços acidentais são erros do usuário.
     * "usuario@email.com " (com espaço) nunca deveria existir no banco mas é melhor corrigir silenciosamente do que rejeitar.
     * A regra 'email' rejeitaria " usuario@email.com" de qualquer forma, então damos trim antes pra dar uma chance ao usuário que digitou com espaço acidental.
     *
     * Senha: espaços podem ser INTENCIONAIS.
     * Alguém pode ter definido a senha como "  minha senha segura  " com espaços nas pontas, sei lá.
     * Se fizermos trim() silenciosamente, a senha "  abc  " vira "abc" e o login começa a falhar sem explicação, e a pessoa não entende por que a senha não funciona.
     * Regra geral: nunca modifique a senha sem o usuário saber.
     *
     * Pesquise "password handling best practices", "never trim passwords".
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => trim((string) $this->input('email', '')),
            // 'password' intencionalmente fora daqui
        ]);
    }

    public function rules(): array
    {
        return [
            /*
             * A regra 'email' do Laravel é mais rigorosa que filter_var() do PHP puro.
             * O email já chegou trimmado de prepareForValidation().
             */
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ];
    }
}
