<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adiciona soft delete em usuarios e viacoes.
// SoftDeletes no Eloquent usa a coluna deleted_at:
// - null = registro ativo (padrão em todas as queries)
// - timestamp = registro excluído (invisível por padrão, acessível via withTrashed()/onlyTrashed())
// Pesquise "Laravel soft deletes", "SoftDeletes trait".

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // softDeletes() cria a coluna deleted_at TIMESTAMP NULL DEFAULT NULL.
            // O Eloquent filtra automaticamente WHERE deleted_at IS NULL em todas as queries.
            $table->softDeletes();
        });

        Schema::table('viacoes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('viacoes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
