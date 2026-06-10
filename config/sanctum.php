<?php

declare(strict_types=1);

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    /*
     * SANCTUM_STATEFUL_DOMAINS:
     * Define quais hosts podem autenticar por cookie/sessão (fluxo SPA first-party).
     * Mesmo usando Bearer token neste projeto, manter isso explícito evita surpresa quando alguém testar Sanctum com frontend local (localhost, 127.0.0.1, etc).
     */
    'stateful'     => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
        // Sanctum::currentRequestHost(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    /*
     * guard = ['web']:
     * Primeiro o Sanctum tenta autenticar por sessão (guard web).
     * Se não houver sessão válida, ele cai para Bearer token automaticamente.
     *
     * Isso mantém o comportamento padrão do Laravel e facilita comparar com o projeto PHP puro.
     * Antes era um fluxo manual, agora é pipeline de guards.
     */
    'guard'        => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. This will override any values set in the token's
    | "expires_at" attribute, but first-party sessions are not affected.
    |
    */

    /*
     * Token sem expiração é conveniente pra demo, mas aumenta risco se vazar.
     * Aqui adotamos expiração padrão de 120 minutos (configurável por .env).
     *
     * Se quiser "sem expiração" em teste local, setar: SANCTUM_TOKEN_EXPIRATION=null
     */
    'expiration'   => env('SANCTUM_TOKEN_EXPIRATION', 120),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | security scanning initiatives maintained by open source platforms
    | that notify developers if they commit tokens into repositories.
    |
    | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
    |
    */

    /*
     * Prefixo de token:
     * Ajuda ferramentas de secret scanning (GitHub, etc) a detectar vazamento de token em commits, logs ou snippets compartilhados.
     */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'vdl_'),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    /*
     * Middleware interno do Sanctum no fluxo stateful (cookie):
     * - encrypt_cookies: protege cookie
     * - validate_csrf_token: defesa CSRF para requests stateful
     * - authenticate_session: sincroniza sessão autenticada
     *
     * No fluxo Bearer token (nosso caso principal), o middleware relevante é auth:sanctum nas rotas,
     * mas manter este bloco explicado ajuda interns a entender o "modo SPA" do Sanctum.
     */
    'middleware'   => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies'      => EncryptCookies::class,
        'validate_csrf_token'  => PreventRequestForgery::class,
    ],

];
