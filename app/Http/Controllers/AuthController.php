<?php

// Controller de autenticação: compare com src/Controllers/AuthController.php do PHP puro.
// A lógica é a mesma, mas o Laravel abstrai toda a mecânica de sessão:
//   PHP puro: session_start(), session_regenerate_id(), $_SESSION, session_destroy()
//   Laravel:  Auth::attempt(), $request->session()->regenerate(), Auth::logout()
// Pesquise "Laravel Auth facade", "session management Laravel".

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(): View
    {
        return view('auth.login', [
            'title' => 'Entrar: Viações Demo',
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        /*
         * LoginRequest valida email e senha antes de chegar aqui.
         * Se o email estiver mal formatado ou a senha em branco, o Laravel redireciona automaticamente com erros sem nem tentar autenticar.
         */
        $credentials = $request->only('email', 'password');

        /*
         * Auth::attempt():
         * - busca o usuário pelo email na tabela configurada em config/auth.php
         * - chama password_verify($credentials['password'], $user->getAuthPassword())
         * - se correto, inicia a sessão autenticada
         */
        if (Auth::attempt($credentials)) {
            /*
             * session()->regenerate() gera um novo ID de sessão após o login.
             * Pesquise "session fixation", "session hijacking".
             */
            $request->session()->regenerate();

            return redirect()->intended(route('viacoes.index'))->with('success', 'Login efetuado.');
        }

        /*
         * Erro proposital vago, não dizemos se foi o email ou a senha que errou (pesquise "user enumeration attack").
         * onlyInput('email'): só manda o email de volta ao formulário, nunca a senha.
         */
        return back()
            ->withErrors(['email' => 'E-mail ou senha incorretos.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); // protege o CSRF token

        return redirect()->route('home')->with('success', 'Logout realizado.');
    }
}
