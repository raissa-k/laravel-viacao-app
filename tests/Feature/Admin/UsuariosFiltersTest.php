<?php

// Exemplos: filtros na listagem (mostrar somente deletados com ?deletado=1)

use App\Models\Usuario;

it('mostra apenas usuários deletados quando ?deletado=1', function () {
    $actor = Usuario::factory()->create();

    $active = Usuario::factory()->create(['nome' => 'Active Person']);
    $toTrash = Usuario::factory()->create(['nome' => 'ToBeTrash']);

    // Soft delete one
    $this->actingAs($actor)->delete(route('usuarios.destroy', $toTrash));

    // Default listing não deve incluir o removido
    $resp = $this->actingAs($actor)->get(route('usuarios.index'));
    $ids = array_map(fn ($u) => $u->id, $resp->viewData('usuarios')->items());
    expect($ids)->not->toContain($toTrash->id);

    // Listagem com deletado=1 deve retornar os removidos
    $respTrashed = $this->actingAs($actor)->get(route('usuarios.index', ['deletado' => 1]));
    $idsTrashed = array_map(fn ($u) => $u->id, $respTrashed->viewData('usuarios')->items());
    expect($idsTrashed)->toContain($toTrash->id);
});
