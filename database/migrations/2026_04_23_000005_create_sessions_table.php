<?php

declare(strict_types=1);

// Migration da tabela de sessões usada quando SESSION_DRIVER=database.
// No PHP puro: sessões eram arquivos gerenciados pelo runtime nativo (session_start(), $_SESSION).
// No Laravel: com driver "database", cada sessão é uma linha nesta tabela.
// Vantagens sobre arquivos: consultas por usuário, expiração via query, múltiplos servidores.
// Pesquise "Laravel session drivers", "distributed session storage".

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // user_id nullable: sessões de visitantes não autenticados também são armazenadas.
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
