<?php


declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Viacao;
use App\Services\TransporteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SincronizarViacoes extends Command
{
    protected $signature   = 'viacoes:sincronizar';
    protected $description = 'Consome a API de transporte e sincroniza as operadoras locais usando upsert';

    public function __construct(private readonly TransporteService $transporteService)
    {
        parent::__construct();
    }
    public function handle(): int
    {
        $this->info('Iniciando a busca de operadoras na API...'); //apenas uma mensagem
        $operadorasApi = $this->transporteService->listarTodasOperadoras();
        if (empty($operadorasApi)) {
            $this->error('Nenhuma operadora foi retornada da API ou ocorreu um erro na busca.');


            return self::FAILURE;
        }

        $apiIds        = array_column($operadorasApi, 'id');
        $idsExistentes = Viacao::whereIn('api_id', $apiIds)
        ->pluck('api_id')
        ->toArray();

        $inseridos     = 0;
        $atualizados   = 0;

        $this->info('Processando e verificando dados da API...');

        foreach ($operadorasApi as $operadora) {
            if (empty($operadora['nome'])) {
                continue;
            }
            $viacao = Viacao::where('api_id', $operadora['id'])->first();

            if ($viacao === null) {
                $viacao = Viacao::where('nome', $operadora['nome'])->first();
            }

            if ($viacao !== null) {
                $atualizados++;

                $viacao->api_id    = $operadora['id'];
                $viacao->site      = $operadora['site']           ?? $viacao->site;
                $viacao->cidade_id = $operadora['sede_cidade_id'] ?? $viacao->cidade_id;
                $viacao->ativa     = $operadora['ativo']          ?? $viacao->ativa ?? true;

            } else {
                $inseridos++;
                $viacao            = new Viacao();
                $viacao->nome      = $operadora['nome'];
                $viacao->api_id    = $operadora['id'];
                $viacao->site      = $operadora['site']           ?? null;
                $viacao->cidade_id = $operadora['sede_cidade_id'] ?? null;
                $viacao->ativa     = $operadora['ativo']          ?? true;
            }

            try {
                $viacao->save();
            } catch (\Throwable $e) {
                Log::error('Falha ao salvar viação individualmente', [
                    'nome' => $operadora['nome'],
                    'erro' => $e->getMessage()
                ]);
                $this->error("Erro ao salvar a viação [{$operadora['nome']}]: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Sincronização concluída com sucesso!");
        $this->line("<info>Inseridas:</info> {$inseridos}");
        $this->line("<info>Atualizadas:</info> {$atualizados}");

        return self::SUCCESS; //retorna 1
    }
}
