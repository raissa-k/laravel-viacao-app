<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// No PHP puro, esse schema fica em src/database/init.sql e roda via Docker entrypoint.
// No Laravel, migrations são classes PHP versionadas.
// Você roda "php artisan migrate" e o framework controla quais já foram executadas (tabela migrations no banco).
// Pesquise "Laravel migrations" e compare com o init.sql do projeto PHP.

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            // Equivalente a: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->id();
            $table->string('nome');
            // unique() garante que não haverá dois usuários com o mesmo email, assim como o UNIQUE do init.sql.
            // O Laravel cria o index automaticamente.
            $table->string('email')->unique();
            // Armazena o hash bcrypt. No PHP puro era "senha VARCHAR(255) NOT NULL".
            // Aqui mantemos o mesmo nome de coluna pra facilitar a comparação.
            $table->string('senha');
            // Laravel usa created_at + updated_at por padrão via timestamps().
            // A tabela do PHP puro tem só created_at, mas aqui adicionamos updated_at pra seguir a convenção do Eloquent sem complicar o model.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
