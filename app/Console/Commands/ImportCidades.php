<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Cidade;
use App\Services\TransporteService;
use Illuminate\Console\Command;

class ImportCidades extends Command
{
    protected $signature   = 'cidades:importar';

    protected $description = 'Importa cidades da API de transporte e faz upsert na tabela local (api_id e uf)';

    public function __construct(private readonly TransporteService $transporteService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $cidades     = $this->transporteService->listarTodasCidades();

        if (empty($cidades)) {
            $this->error('Nenhuma cidade retornada pela API. Verifique as credenciais e a conectividade.');

            return self::FAILURE;
        }

        $inseridas   = 0;
        $atualizadas = 0;

        foreach ($cidades as $cidade) {
            $model = Cidade::updateOrCreate(
                ['nome' => $cidade['nome']],
                ['api_id' => $cidade['id'], 'uf' => $cidade['uf']]
            );

            if ($model->wasRecentlyCreated) {
                $inseridas++;
            } else {
                $atualizadas++;
            }
        }

        $this->info("Importação concluída: inseridas={$inseridas}, atualizadas={$atualizadas}");

        return self::SUCCESS;
    }
}
