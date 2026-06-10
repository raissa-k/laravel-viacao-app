<?php

declare(strict_types=1);

use App\Models\Usuario;

// Configuração de autenticação do Laravel.
// No PHP puro, isso seria o AuthService + AuthMiddleware manuais.
// Aqui declaramos QUAL model e QUAL mecanismo de sessão usar, e o Laravel cuida do resto.

return [

    'defaults'         => [
        'guard'     => 'web',
        'passwords' => 'usuarios',
    ],

    'guards'           => [
        'web' => [
            // driver session: autentica via cookie de sessão (igual ao PHP puro)
            'driver'   => 'session',
            'provider' => 'usuarios',
        ],
    ],

    'providers'        => [
        'usuarios' => [
            // driver eloquent: usa o Model abaixo pra buscar o usuário por email
            'driver' => 'eloquent',
            'model'  => Usuario::class,
        ],
    ],

    'passwords'        => [
        'usuarios' => [
            'provider' => 'usuarios',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
