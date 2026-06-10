<?php

declare(strict_types=1);

// Migration da tabela de tokens do Sanctum.
// Sanctum armazena tokens hasheados, o plain text só existe no momento da criação.
// Pesquise "Laravel Sanctum", "API token hashing".

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            // morphs(): relação polimórfica, o token pode pertencer a qualquer model que use HasApiTokens (no nosso caso, Usuario).
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
