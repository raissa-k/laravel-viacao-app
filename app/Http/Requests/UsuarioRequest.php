<?php

declare(strict_types=1);

// FormRequest de usuário: valida criação e edição.
// A distinção create vs update é feita por $this->route('usuario'):
//   null   = rota de criação -> senha obrigatória, e-mail único globalmente
//   model  = rota de edição -> senha opcional, e-mail único exceto para si mesmo
// Pesquise "Laravel unique ignore", "Rule::unique", "sometimes vs nullable".

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // rotas protegidas pelo middleware 'auth'
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario'); // null no create, model no update

        return [
            'nome'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                /*
                 * Rule::unique() com ->ignore() garante que o próprio e-mail do usuário não seja rejeitado na edição.
                 * Sem o ignore(), editar qualquer campo sem mudar o e-mail falharia validação.
                 * Pesquise "Rule::unique()->ignore()", "email uniqueness update".
                 */
                Rule::unique('usuarios', 'email')->ignore($usuario?->id)->whereNull('deleted_at'),
            ],
            /*
             * Criação: senha obrigatória (novo usuário precisa de acesso).
             * Edição: 'nullable' + 'sometimes' = só valida SE enviada; em branco = mantém a atual.
             * Password::min(8): regra mínima de segurança.
             * confirmed: exige campo senha_confirmation com o mesmo valor.
             */
            'senha' => $usuario === null
                ? ['required', 'string', Password::min(8), 'confirmed']
                : ['nullable', 'sometimes', 'string', Password::min(8), 'confirmed'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome'  => trim((string) $this->input('nome', '')),
            'email' => trim((string) $this->input('email', '')),
        ]);
    }

    public function messages(): array
    {
        return [
            'nome.required'   => 'O nome é obrigatório.',
            'email.required'  => 'O e-mail é obrigatório.',
            'email.email'     => 'Informe um e-mail válido.',
            'email.unique'    => 'Este e-mail já está em uso.',
            'senha.required'  => 'A senha é obrigatória.',
            'senha.min'       => 'A senha deve ter no mínimo 8 caracteres.',
            'senha.confirmed' => 'A confirmação de senha não confere.',
        ];
    }
}
