<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Telescope é uma ferramenta mantida neste projeto apenas como debug local.
        //
        // Por que registrar aqui em vez de bootstrap/providers.php?
        // O arquivo providers.php é carregado incondicionalmente em todos os ambientes.
        // Registrar aqui com um guard de ambiente garante que o Telescope só exista quando APP_ENV=local.
        //
        // Dois providers são necessários:
        // - TelescopeServiceProvider (do vendor): registra as rotas /telescope, migrations e watchers
        // - App\Providers\TelescopeServiceProvider (nosso): configura o gate de acesso e os filtros
        //
        // Pesquise: "Laravel Telescope local only", "ServiceProvider::register()", "APP_ENV".
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        /*
         * Rate limits da API separados leitura vs escrita.
         *
         * Por que separar?
         * - Leitura é naturalmente mais frequente (listas, polling, etc), então limite mais alto.
         * - Escrita muda estado do sistema e é onde costumam se concentrar tentativas de abuso, então limite mais baixo.
         *
         * Chave de limitação:
         * - leitura: por IP (usuário pode ser visitante)
         * - escrita: por usuário autenticado + IP (evita que um usuário afete outro)
         *
         * Os limites são configuráveis por .env via config/api.php:
         * API_RATE_LIMIT_READ e API_RATE_LIMIT_WRITE.
         *
         * Por que config() e não env() diretamente?
         * env() lê do arquivo .env, mas quando você roda "php artisan config:cache" (padrão em produção para performance), o .env deixa de ser carregado e env() retorna null.
         * config() lê do cache de configuração e sempre funciona, use config() em código de aplicação e env() dentro de arquivos config/*.php.
         * Pesquise: "Laravel config cache", "env() vs config()", "php artisan config:cache".
         */
        RateLimiter::for('api-read', function (Request $request): Limit {
            $perMinute = max(1, (int) config('api.rate_limit_read', 60));

            return Limit::perMinute($perMinute)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Muitas requisições. Tente novamente em instantes.',
                    ], 429, $headers);
                });
        });

        RateLimiter::for('api-write', function (Request $request): Limit {
            $perMinute = max(1, (int) config('api.rate_limit_write', 20));
            $identity = $request->user()?->getAuthIdentifier() ?? 'guest';

            return Limit::perMinute($perMinute)
                ->by($identity.'|'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Limite de escrita excedido. Aguarde 1 minuto.',
                    ], 429, $headers);
                });
        });
    }
}
