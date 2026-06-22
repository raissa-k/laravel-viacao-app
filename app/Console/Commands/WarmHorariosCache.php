<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Viacao;
use App\Services\TransporteService;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;

class WarmHorariosCache extends Command
{
    protected $signature   = 'viacao:warm-cache-horarios';
    protected $description = 'Preenche o cache de horários para todas as linhas das viações ativas em background';

    public function handle(TransporteService $transporteService): int
    {
        $this->info('Iniciando o Pre-Warming do cache de horários...');

        $viacoes = Viacao::where('ativa', true)->get();

        if ($viacoes->isEmpty()) {
            $this->info('Nenhuma viação ativa encontrada no banco local.');
            return Command::SUCCESS;
        }

        foreach ($viacoes as $viacao) {
            if (empty($viacao->api_id)) {
                $this->warn("Viação [{$viacao->nome}] sem api_id. Pulando...");
                continue;
            }

            $this->info("Buscando linhas ativas para a Viação: {$viacao->nome} (API ID: {$viacao->api_id})");

            $linhas   = $transporteService->listarTodasLinhasAtivasPorOperadora((int) $viacao->api_id);

            if (empty($linhas)) {
                $this->comment("Nenhuma linha ativa retornada para a viação {$viacao->nome}.");
                continue;
            }

            // Inicializa o progress usando o Laravel Prompts de forma iterativa
            $progress = progress(
                label: "Processando linhas da viação {$viacao->nome}",
                steps: count($linhas)
            );

            $progress->start();

            foreach ($linhas as $linha) {
                $linhaId = $linha['id'] ?? null;

                if ($linhaId) {
                    $transporteService->listarHorariosDaLinha((int) $linhaId);
                    usleep(50000);
                }

                // Avança a barra do Prompts
                $progress->advance();
            }

            // Finaliza a barra do Prompts
            $progress->finish();
        }

        $this->info('Pre-Warming de cache finalizado com sucesso!');
        return Command::SUCCESS;
    }
}
