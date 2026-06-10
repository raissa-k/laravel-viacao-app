<?php

declare(strict_types=1);

// Configurações da API REST.
//
// Por que um arquivo de config separado?
// Valores lidos em código de aplicação devem vir de config(), não de env() diretamente.
// env() falha silenciosamente quando o config cache está ativo (php artisan config:cache),
// retornando null em vez do valor do .env. config() sempre funciona.
//
// A responsabilidade de ler env() fica aqui, no arquivo de config.
// O código de aplicação (AppServiceProvider, controllers) usa config('api.*').
//
// Pesquise: "Laravel config files", "config cache", "env() vs config()".

return [

    // Máximo de requisições de leitura por minuto, por IP.
    // Aplicado ao grupo de rotas públicas da API (GET /api/viacoes etc).
    'rate_limit_read'  => (int) env('API_RATE_LIMIT_READ', 60),

    // Máximo de requisições de escrita por minuto, por usuário+IP.
    // Aplicado às rotas protegidas da API (POST, PUT, DELETE).
    'rate_limit_write' => (int) env('API_RATE_LIMIT_WRITE', 20),

];
