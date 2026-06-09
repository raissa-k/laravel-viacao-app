<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cria a coluna permitindo null temporariamente
        Schema::table('viacoes', function (Blueprint $table) {
            $table->foreignId('cidade_id')->nullable()->constrained('cidades');
        });

        // 2. Backfill: pega nomes distintos para evitar duplicar cidades
        $nomes = DB::table('viacoes')->whereNotNull('cidade')->distinct()->pluck('cidade');

        // Insere cada cidade única na tabela cidades (apenas as que ainda não existem)
        $cidadeMap = [];
        foreach ($nomes as $nome) {
            $cidade = DB::table('cidades')->where('nome', $nome)->first();

            if ($cidade) {
                $cidadeMap[$nome] = $cidade->id;
            } else {
                $cidadeMap[$nome] = DB::table('cidades')->insertGetId([
                    'nome'       => $nome,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Atualiza cada viação com o cidade_id correspondente
        foreach ($cidadeMap as $nome => $cidadeId) {
            DB::table('viacoes')
                ->where('cidade', $nome)
                ->update(['cidade_id' => $cidadeId]);
        }

        // 3. Remove a coluna antiga
        Schema::table('viacoes', function (Blueprint $table) {
            $table->dropColumn('cidade');
        });
    }

    public function down(): void
    {
        // 1. Recria a coluna string antiga
        Schema::table('viacoes', function (Blueprint $table) {
            $table->string('cidade')->nullable();
        });

        // 2. Reverte o backfill
        $viacoes = DB::table('viacoes')->whereNotNull('cidade_id')->select('id', 'cidade_id')->get();

        foreach ($viacoes as $viacao) {
            $cidade = DB::table('cidades')->where('id', $viacao->cidade_id)->first();

            if ($cidade) {
                DB::table('viacoes')
                    ->where('id', $viacao->id)
                    ->update(['cidade' => $cidade->nome]);
            }
        }

        // 3. Remove a coluna nova
        Schema::table('viacoes', function (Blueprint $table) {
            $table->dropForeign(['cidade_id']);
            $table->dropColumn('cidade_id');
        });
    }
};
