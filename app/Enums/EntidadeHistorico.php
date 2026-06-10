<?php

declare(strict_types=1);

// Enum das entidades auditadas na tabela historico.
//
// O morph map em AppServiceProvider usa ->value de cada case como chave.
// Isso garante que o alias gravado no banco (entidade_type = 'viacao') e o valor usado nos filtros (EntidadeHistorico::Viacao) nunca desincronizam.
//
// Se você adicionar uma nova entidade rastreada nos logs:
// 1. Adicione um case aqui.
// 2. Registre o alias no morph map (AppServiceProvider::boot).
// 3. Adicione morphMany(Historico::class, 'entidade') no model novo.
//
// Por que não usar a string diretamente?
// Sem enum: 'viacao', 'Viacao', 'viacoes', 'viassaum' são strings igualmente aceitas pelo PHP.
// Com enum: EntidadeHistorico::tryFrom('Viacao') retorna null (valor inválido).
// Pesquise "backed enum PHP 8.1", "enum vs string constants".

namespace App\Enums;

enum EntidadeHistorico: string
{
    case Viacao  = 'viacao';
    case Usuario = 'usuario';
}
