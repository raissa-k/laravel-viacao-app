<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Configuração global do Pest.
// uses(): aplica traits a todos os testes de um diretório sem repetir em cada arquivo.
// RefreshDatabase: recria o banco (SQLite in-memory) a cada teste, garantindo isolamento.
// Pesquise "Pest PHP", "Laravel testing with Pest", "RefreshDatabase trait".

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Feature', 'Unit');
