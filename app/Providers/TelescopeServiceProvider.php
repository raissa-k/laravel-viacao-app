<?php

declare(strict_types=1);

// Configuração do Laravel Telescope.
//
// O QUE É O TELESCOPE?
// Telescope é um painel de debug local que registra requests HTTP, queries SQL, jobs, exceptions, logs, e-mails, eventos, etc.
// Acesse em: http://localhost:8082/telescope
//
// POR QUE --dev?
// O Telescope só existe no ambiente local. Adicionr como dependência de produção consume recursos e expõe informações internas desnecessariamente.
// O composer.json separa dependências de produção (require) das de desenvolvimento (require-dev).
// Pesquise: "composer require-dev", "Laravel Telescope local-only".

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        $this->hideSensitiveRequestDetails();

        // Registra TODAS as entradas no ambiente local (o default filtraria apenas erros).
        // Em produção, poderia registrar apenas exceptions, requests/jobs que falharam e tasks agendadas.
        Telescope::filter(function (IncomingEntry $entry): bool {
            return $this->app->environment('local') ||
                   $entry->isReportableException()  ||
                   $entry->isFailedRequest()        ||
                   $entry->isFailedJob()            ||
                   $entry->isScheduledTask()        ||
                   $entry->hasMonitoredTag();
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        // No ambiente local, mostramos tudo pra facilitar o debug.
        // Em outros ambientes, padrão esconder tokens e cookies dos logs.
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);
        Telescope::hideRequestHeaders(['cookie', 'x-csrf-token', 'x-xsrf-token']);
    }

    protected function gate(): void
    {
        // Gate de acesso ao painel /telescope.
        //
        // No ambiente local (único onde o Telescope é carregado), qualquer usuário pode acessar.
        // Não há dado sensível de produção aqui.
        //
        // Pesquise: "Laravel Gate", "Gate::define viewTelescope".
        Gate::define('viewTelescope', function (Usuario $user): bool {
            return $this->app->environment('local');
        });
    }
}
