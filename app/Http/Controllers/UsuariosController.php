<?php

declare(strict_types=1);

// Controller de usuários: CRUD completo + show (com histórico) + restore (soft delete).
// Padrão idêntico ao ViacaoController: DTO de filtro, service layer, redirect com flash.

namespace App\Http\Controllers;

use App\DTOs\UsuarioFilterDTO;
use App\Http\Requests\UsuarioRequest;
use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuariosController extends Controller
{
    public function __construct(
        private readonly UsuarioService $usuarioService,
    ) {
    }

    public function index(Request $request): View
    {
        // UsuarioFilterDTO mantém o mesmo padrão dos outros controllers de listagem: nenhum controller lê $request->get() diretamente, sempre via DTO.
        $filter   = UsuarioFilterDTO::fromRequest($request);
        $usuarios = $this->usuarioService->all($filter);

        return view('admin.usuarios.index', [
            'title'    => 'Usuários',
            'usuarios' => $usuarios,
            'filter'   => $filter,
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.create', [
            'title' => 'Cadastrar Usuário',
        ]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $data    = $request->validated();
        $usuario = $this->usuarioService->create($data['nome'], $data['email'], $data['senha'], auth()->id());

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado (#'.$usuario->id.').');
    }

    /** Exibe um único usuário com seu histórico de alterações. */
    public function show(Usuario $usuario): View
    {
        $historico = $usuario->historico()->with('ator')->orderByDesc('criado_em')->get();

        return view('admin.usuarios.show', [
            'title'     => 'Usuário: '.$usuario->nome,
            'usuario'   => $usuario,
            'historico' => $historico,
        ]);
    }

    public function edit(Usuario $usuario): View
    {
        return view('admin.usuarios.edit', [
            'title'   => 'Editar Usuário',
            'usuario' => $usuario,
        ]);
    }

    public function update(UsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $data      = $request->validated();
        // senha null quando o campo foi deixado em branco na edição, pra manter a senha anterior
        $novaSenha = filled($data['senha'] ?? null) ? $data['senha'] : null;

        $this->usuarioService->update($usuario, $data['nome'], $data['email'], $novaSenha, auth()->id());

        return redirect()->route('usuarios.show', $usuario)->with('success', 'Usuário atualizado.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        // Impede auto-exclusão: o usuário logado não pode excluir a si mesmo.
        if ($usuario->id === auth()->id()) {
            return back()->with('danger', 'Você não pode excluir seu próprio usuário.');
        }

        $this->usuarioService->delete($usuario, auth()->id());

        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído (pode ser restaurado).');
    }

    /** Restaura um usuário soft-deleted. */
    public function restore(int $id): RedirectResponse
    {
        $usuario = Usuario::withTrashed()->findOrFail($id);
        $this->usuarioService->restore($usuario, auth()->id());

        return redirect()->route('usuarios.index')->with('success', 'Usuário restaurado.');
    }
}
