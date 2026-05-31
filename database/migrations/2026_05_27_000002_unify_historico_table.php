<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Transforma viacoes_historico em uma tabela genérica de auditoria.
//
// Por que uma única tabela?
// - Evita duplicar a estrutura (acao, alteracoes, usuario_id, criado_em) pra cada entidade.
// - Queries de historico global (ex: "o que mudou hoje?") usam uma só tabela.
// - Pesquise "audit log table design", "single-table audit", "polymorphic audit".
//
// Colunas novas:
//   entidade    - tipo da entidade alterada: 'viacao' ou 'usuario'
//   entidade_id - ID da entidade alterada (substitui viacao_id)
//
// O campo usuario_id continua existindo mas e é sempre o ator (quem fez a alteração)

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viacoes_historico', function (Blueprint $table) {
            // entidade_type / entidade_id são as colunas-padrão de relações polimórficas no Laravel.
            // entidade_type armazena o alias do morph map ('viacao', 'usuario'),
            // entidade_id armazena o PK da entidade alterada.
            $table->string('entidade_type', 64)->nullable()->after('id');
            $table->unsignedBigInteger('entidade_id')->nullable()->after('entidade_type');
        });

        // Backfill: todos os registros existentes são de viacoes.
        DB::table('viacoes_historico')->update([
            'entidade_type' => 'viacao',
            'entidade_id' => DB::raw('viacao_id'),
        ]);

        Schema::table('viacoes_historico', function (Blueprint $table) {
            // Index composto pra queries do tipo "historico desta viacao" e "historico deste usuario".
            $table->index(['entidade_type', 'entidade_id']);

            // viacao_id não existe mais como campo estruturado: a informação migrou pra entidade_id.
            $table->dropIndex('viacoes_historico_viacao_id_index');
            $table->dropColumn('viacao_id');
        });

        // Renomeia pra refletir o escopo genérico.
        Schema::rename('viacoes_historico', 'historico');
    }

    public function down(): void
    {
        Schema::rename('historico', 'viacoes_historico');

        Schema::table('viacoes_historico', function (Blueprint $table) {
            $table->unsignedBigInteger('viacao_id')->nullable()->after('id');
            $table->dropIndex(['entidade_type', 'entidade_id']);
            $table->dropColumn(['entidade_type', 'entidade_id']);
            $table->index('viacao_id');
        });

        DB::table('viacoes_historico')->update([
            'viacao_id' => DB::raw('usuario_id'), // tentativa de reversão, mas dado exato pode não ser recuperado
        ]);
    }
};
