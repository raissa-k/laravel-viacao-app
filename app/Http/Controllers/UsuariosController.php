<?php

// Controller de usuários: listagem somente leitura.
// No PHP puro: usava UsuarioRepository diretamente (sem service, pois não há lógica de negócio).
// No Laravel: mesma decisão, usamos o model Eloquent diretamente no controller.
// Quando tivermos um CRUD de usuário, faz sentido mover tudo pra lá.

namespace App\Http\Controllers;

use App\DTOs\UsuarioFilterDTO;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuariosController extends Controller
{
    public function index(Request $request): View
    {
        // UsuarioFilterDTO mantém o mesmo padrão dos outros controllers de listagem: nenhum controller lê $request->get() diretamente, sempre via DTO.
        $filter = UsuarioFilterDTO::fromRequest($request);

        $usuarios = Usuario::query()
            ->when($filter->q !== '', function ($query) use ($filter) {
                $escaped = addcslashes($filter->q, '%_');
                $query->where(function ($q2) use ($escaped) {
                    $q2->where('nome', 'like', '%'.$escaped.'%')
                        ->orWhere('email', 'like', '%'.$escaped.'%');
                });
            })
            ->orderBy('id')
            ->get();

        return view('admin.usuarios.index', [
            'title' => 'Usuários',
            'usuarios' => $usuarios,
            'filter' => $filter,
        ]);
    }
}
