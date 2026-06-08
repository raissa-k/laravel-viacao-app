<?php
// Comando Artisan equivalente ao cli/import_viacoes.php do PHP puro.
// PHP puro:  php src/cli/import_viacoes.php viacao_data.json
// Laravel:   php artisan viacoes:import viacao_data.json
//
// A lógica: lê JSON, valida cada linha, cria as válidas, pula as inválidas.
// A diferença principal está na validação:
//   PHP puro: ViacaoValidator::validate(), classe manual com if/else
//   Laravel:  Validator::make(), o mesmo motor que o FormRequest usa internamente
//
// FormRequest não pode ser usado aqui porque ele depende de contexto HTTP (request, sessão).
// Pesquise "Laravel Validator facade", "Artisan commands", "php artisan make:command".
namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Viacao;
use App\Http\Requests\ViacaoApiRequest;
use app\Services\ViacaoService;

#[Signature('viacoes:export {filename : Digite o nome do arquivo JSON de destino}')]
#[Description('Exporta as TODAS as viações,ativas e inativas,excluídas não...')]
class ExportViacoes extends Command
{
    public function __construct(private readonly ViacaoService $viacaoService)
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        $this->error('Algo deu errado.'); //normalmente se dar certo ele retorna 0,se errado 1

        return 1;
    }
}
