<?php

// Testes do comando Artisan viacoes:import.
// Artisan::call() executa o comando inline e retorna o exit code.
// É mais direto que $this->artisan() (PendingCommand) e não tem problemas de interação com buffers de saída em alguns ambientes de teste.
// Pesquise "Laravel Artisan::call", "artisan command testing".

use App\Models\Viacao;
use App\Models\ViacaoHistorico;
use Illuminate\Support\Facades\Artisan;

it('importa viações válidas de um arquivo JSON', function () {
    $file = tempnam(sys_get_temp_dir(), 'viacoes_').'.json';
    file_put_contents($file, json_encode([
        ['nome' => 'Cometa',  'cidade' => 'Campinas', 'ativa' => true],
        ['nome' => 'Eucatur', 'cidade' => 'Curitiba'],
    ]));

    $exitCode = Artisan::call('viacoes:import', ['file' => $file]);

    expect($exitCode)->toBe(0)
        ->and(Viacao::count())->toBe(2)
        ->and(ViacaoHistorico::where('acao', 'Criado')->count())->toBe(2);

    unlink($file);
});

it('pula linhas inválidas e continua', function () {
    $file = tempnam(sys_get_temp_dir(), 'viacoes_').'.json';
    file_put_contents($file, json_encode([
        ['nome' => 'Válida', 'cidade' => 'SP'],
        ['nome' => '',       'cidade' => 'SP'],  // inválida: nome vazio
        ['cidade' => 'RJ'],                       // inválida: sem nome
    ]));

    $exitCode = Artisan::call('viacoes:import', ['file' => $file]);

    expect($exitCode)->toBe(0)
        ->and(Viacao::count())->toBe(1);

    unlink($file);
});

it('retorna falha para arquivo inexistente', function () {
    $exitCode = Artisan::call('viacoes:import', ['file' => '/nao/existe.json']);

    expect($exitCode)->toBe(1); // Command::FAILURE
});

it('retorna falha para JSON inválido', function () {
    $file = tempnam(sys_get_temp_dir(), 'viacoes_').'.json';
    file_put_contents($file, 'isso nao e json');

    $exitCode = Artisan::call('viacoes:import', ['file' => $file]);

    expect($exitCode)->toBe(1);

    unlink($file);
});
