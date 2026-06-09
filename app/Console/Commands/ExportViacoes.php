<?php

namespace App\Console\Commands;

use App\Services\ViacaoService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('viacoes:export {filename : Digite o nome do arquivo JSON de destino}')] // o # tem a mesma valia do declarado no import
#[Description('Exporta as TODAS as viações,ativas e inativas,excluídas não...')]
class ExportViacoes extends Command
{
    public function __construct(private readonly ViacaoService $viacaoService)
    {
        parent::__construct();
    }

    public function handle(): int
    {

        $filename = $this->argument('filename');

        // caso o usuário não digite .json, é adicionado
        if (! str_ends_with($filename, '.json')) {
            $filename .= '.json';
        }
        try {
            // busca as viacoes com o metodo que criei la no service,no outro era para importar
            $viacoes = $this->viacaoService->exportViacoes();

            if ($viacoes->isEmpty()) {
                $this->warn('Não achei nada para exportar'); // warn é apenas um tipo de string exclusivo pra avisos ou warnings

                return self::SUCCESS;
            }

            // manipula o json armazenado em $viacoes(doService),usando as flags/constantes JSON_PRETTY_PINT e JSON_UNESCAPEWD_UNICODE
            $jsonText = json_encode($viacoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            //bloco if que se o json der falha(false) ele ja manda pro demonio
            if ($jsonText === false) {
                $this->error('Algo deu zika');
                $this->error(json_last_error_msg());
                return self::FAILURE;
            }

            $rightPath = storage_path("app/{$filename}");

            // cria o arquivo na máquina e injeta o json
            file_put_contents($rightPath, $jsonText);

            $this->info('deu boa!');

            return self::SUCCESS;
        } catch (\Exception $exception) {

            $this->error('Algo deu errado'); // normalmente se dar certo ele retorna 0,se errado 1
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
