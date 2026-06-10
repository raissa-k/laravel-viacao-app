<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Equivalente ao bloco "Histórico de alterações" do init.sql.
// Ponto de comparação interessante: no init.sql explicamos por que NÃO usamos FOREIGN KEY aqui.
// O Laravel facilitaria criar FKs com ->foreign('viacao_id')->references('id')->on('viacoes'), mas mantemos a mesma decisão do PHP puro.
// Sem FK, pra o histórico sobreviver a exclusões.
// Pesquise "audit log design", "soft delete vs hard delete".

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viacoes_historico', function (Blueprint $table) {
            $table->id();
            // Nullable: a viação pode ter sido excluída (o log sobrevive)
            $table->unsignedBigInteger('viacao_id')->nullable();
            // Nullable: ações feitas via seed não têm usuário logado
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('acao', 64);
            // json(): tipo nativo do MySQL, o banco valida que é JSON válido.
            // Guarda {"before": {...}, "after": {...}} pra rastrear o que mudou.
            // O Eloquent faz cast automático de/para array PHP quando configurado no model.
            $table->json('alteracoes');
            // criado_em em vez de created_at pra manter o mesmo nome do PHP puro e facilitar comparação.
            // useCurrent() = DEFAULT CURRENT_TIMESTAMP, igual ao init.sql.
            $table->timestamp('criado_em')->useCurrent();

            // Indexes: mesma lógica do init.sql (filtros por viacao_id e usuario_id são comuns)
            $table->index('viacao_id');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viacoes_historico');
    }
};
