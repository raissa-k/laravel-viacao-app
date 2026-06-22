<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Inspiring;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('cidades:importar')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/importacoes.log'))
    ->onFailure(function () {
        Log::error('Scheduler: cidades:importar FALHOU', ['hora' => now()->toDateTimeString()]);
    })
    ->onSuccess(function () {
        Log::info('Scheduler: cidades:importar concluído com sucesso', ['hora' => now()->toDateTimeString()]);
    });

Schedule::command('viacoes:sincronizar')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/importacoes.log'))
    ->onFailure(function () {
        Log::error('Scheduler: viacoes:sincronizar FALHOU', ['hora' => now()->toDateTimeString()]);
    })
    ->onSuccess(function () {
        Log::info('Scheduler: viacoes:sincronizar concluído com sucesso', ['hora' => now()->toDateTimeString()]);
    });

Schedule::command('viacao:warm-cache-horarios')->everyThirtyMinutes()->runInBackground();
