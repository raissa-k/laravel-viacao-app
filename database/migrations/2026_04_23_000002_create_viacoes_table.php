<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Equivalente ao bloco "Viações" do init.sql do projeto PHP puro.
// No init.sql: ENGINE=InnoDB + utf8mb4 são declarados explicitamente.
// O Laravel usa InnoDB e utf8mb4 por padrão nas conexões MySQL, configurado em config/database.php.

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cidade');
            // boolean() cria TINYINT(1), igual ao PHP puro. O Eloquent faz o cast pra bool automaticamente.
            // No PHP puro, a conversão é feita no Viacao::fromRow(): (int)($row['ativa'] ?? 0) === 1
            $table->boolean('ativa')->default(true);
            // Nullable: logo é opcional, assim como no init.sql (logo VARCHAR(255) NULL)
            $table->string('logo')->nullable();
            // timestamps() cria created_at e updated_at.
            // No Eloquent o updated_at é gerenciado pelo framework ao chamar save()/update().
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viacoes');
    }
};
