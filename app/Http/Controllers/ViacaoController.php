<?php

// Controller de viações.
// show(): exibe uma viação com seu histórico.
// restore(): restaura soft-deleted via ViacaoService.
// Os demais métodos (index/create/store/edit/update/destroy) são iguais ao original
// com a diferença que destroy() agora é soft delete e all() retorna paginator.

namespace App\Http\Controllers;

use App\DTOs\ViacaoFilterDTO;
use App\Http\Requests\ViacaoRequest;
use App\Models\Viacao;
use App\Services\UploadService;
use App\Services\ViacaoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ViacaoController extends Controller
{
    public function __construct(
        private readonly ViacaoService $viacaoService,
        private readonly UploadService $uploadService,
    ) {}

    /** Lista viações no painel admin, com filtros opcionais de busca e status. */
    public function index(Request $request): View
    {
        // DTO normaliza e tipa os GET params antes de passar pro service.
        $filter = ViacaoFilterDTO::fromRequest($request);
        $viacoes = $this->viacaoService->all($filter);

        return view('admin.viacoes.index', [
            'title' => 'Viações',
            'viacoes' => $viacoes,
            'filter' => $filter,
        ]);
    }

    /** Exibe o formulário de cadastro. */
    public function create(): View
    {
        return view('admin.viacoes.create', [
            'title' => 'Cadastrar Viação',
        ]);
    }

    /** Processa o form de cadastro: valida (via ViacaoRequest), faz upload e salva. */
    public function store(ViacaoRequest $request): RedirectResponse
    {
        $logo = null;

        if ($request->hasFile('logo')) {
            try {
                $logo = $this->uploadService->handleUpload($request->file('logo'));
            } catch (\RuntimeException $e) {
                // Redireciona de volta com erro de upload e mantém os outros campos preenchidos
                return back()->withErrors(['logo' => $e->getMessage()])->withInput();
            }
        }

        $data = $request->validated();
        $viacao = $this->viacaoService->create($data['nome'], $data['cidade'], $logo, $data['ativa'], auth()->id());

        /*
         * redirect()->route() vs View::redirect() do PHP puro:
         * No PHP puro: header('Location: /admin/viacoes') + exit.
         * No Laravel: retorna um RedirectResponse, o framework envia o header.
         * with('success') guarda a mensagem na sessão (flash) por um request.
         */
        return redirect()->route('viacoes.index')->with('success', 'Viação criada com sucesso (#'.$viacao->id.').');
    }

    /**
     * Exibe uma única viação com seu histórico de alterações.
     * Route model binding busca só registros não excluídos.
     */
    public function show(Viacao $viacao): View
    {
        $historico = $viacao->historico()->with('ator')->orderByDesc('criado_em')->get();

        return view('admin.viacoes.show', [
            'title' => 'Viação: '.$viacao->nome,
            'viacao' => $viacao,
            'historico' => $historico,
        ]);
    }

    public function edit(Viacao $viacao): View
    {
        return view('admin.viacoes.edit', [
            'title' => 'Editar Viação',
            'viacao' => $viacao,
        ]);
    }

    /** Processa o form de edição. */
    public function update(ViacaoRequest $request, Viacao $viacao): RedirectResponse
    {
        $logo = $viacao->logo;

        if ($request->hasFile('logo')) {
            try {
                $logo = $this->uploadService->handleUpload($request->file('logo'));
            } catch (\RuntimeException $e) {
                return back()->withErrors(['logo' => $e->getMessage()])->withInput();
            }
        }

        $data = $request->validated();
        $this->viacaoService->update($viacao, $data['nome'], $data['cidade'], $data['ativa'], $logo, auth()->id(), $data['site'] ?? null);

        return redirect()->route('viacoes.show', $viacao)->with('success', 'Viação atualizada.');
    }

    /** Marca a viação como excluída. */
    public function destroy(Viacao $viacao): RedirectResponse
    {
        $this->viacaoService->delete($viacao, auth()->id());

        return redirect()->route('viacoes.index')->with('success', 'Viação excluída (pode ser restaurada).');
    }

    /**
     * Restaura uma viação soft-deleted.
     *
     * Recebe o ID como int porque route model binding padrão não encontra soft-deleted.
     * withTrashed() inclui registros com deleted_at preenchido na query.
     * Pesquise "Eloquent withTrashed", "soft delete restore".
     */
    public function restore(int $id): RedirectResponse
    {
        $viacao = Viacao::withTrashed()->findOrFail($id);
        $this->viacaoService->restore($viacao, auth()->id());

        return redirect()->route('viacoes.index')->with('success', 'Viação restaurada.');
    }
}
