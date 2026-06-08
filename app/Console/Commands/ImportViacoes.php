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

use App\Http\Requests\ViacaoApiRequest;
use App\Services\ViacaoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ImportViacoes extends Command
{
    protected $signature = 'viacoes:import {file : Caminho para o arquivo JSON com as viações}';

    protected $description = 'Importa viações de um arquivo JSON (equivalente ao cli/import_viacoes.php do PHP puro)';

    public function __construct(private readonly ViacaoService $viacaoService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! is_file($file)) {
            $this->error("Arquivo não encontrado: {$file}");

            return self::FAILURE;
        }

        $raw = file_get_contents($file);
        $data = json_decode($raw, true);

        if (! is_array($data)) {
            $this->error('JSON inválido.');

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;

        foreach ($data as $i => $item) {
            /*
             * Validator::make() é o motor interno que o FormRequest usa automaticamente.
             * FormRequest não pode ser usado aqui porque depende de contexto HTTP.
             * ViacaoApiRequest::validationRules() expõe as regras como static method, então CLI e HTTP compartilham as mesmas regras.
             */
            $validator = Validator::make(
                is_array($item) ? $item : [],
                ViacaoApiRequest::coreRules(),
                [
                    'nome.required' => 'O nome é obrigatório.',
                    'nome.max' => 'O nome deve ter no máximo 255 caracteres.',
                    'cidade.required' => 'A cidade é obrigatória.',
                    'cidade.max' => 'A cidade deve ter no máximo 255 caracteres.',
                ]
            );

            if ($validator->fails()) {
                $skipped++;
                $this->warn("Linha {$i}: ".implode('; ', $validator->errors()->all()));

                continue;
            }

            $d = $validator->validated();

            $this->viacaoService->create(
                nome: $d['nome'],
                cidade: $d['cidade'],
                ativa: isset($d['ativa']) ? (bool) $d['ativa'] : true,
                logo: null,
                usuarioId: null, // CLI: sem usuário logado, registra ação sem autoria
            );

            $created++;
            $this->line("Criada: <info>{$d['nome']}</info> ({$d['cidade']})");
        }

        $this->newLine();
        $this->info("Importação concluída: criado={$created}, skipped={$skipped}");

        return self::SUCCESS;
    }
}
