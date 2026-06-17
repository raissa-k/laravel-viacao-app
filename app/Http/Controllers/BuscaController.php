<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CidadeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuscaController extends Controller
{
    public function __construct(private readonly CidadeService $cidadeService)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->filled(['origem', 'destino', 'data'])) {
            return redirect()
                ->route('home')
                ->with('error', 'Por favor, preencha origem, destino e data para realizar a busca.');
        }

        $origem  = $this->cidadeService->find((int) $request->get('origem'));
        $destino = $this->cidadeService->find((int) $request->get('destino'));
        $cidades = $this->cidadeService->all();

        if (!$origem || !$destino) {
            return redirect()
                ->route('home')
                ->with('error', 'Cidade não encontrada.');
        }

        $linhas  = \App\DTOs\LinhaResultadoDTO::fake();
        return view('buscas.index', [
            'linhas'  => $linhas,
            'origem'  => $origem,
            'destino' => $destino,
            'cidades' => $cidades,
        ]);
    }
}
