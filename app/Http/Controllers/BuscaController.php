<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuscaController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // Redireciona para a home se os campos essenciais não estiverem preenchidos
        if (!$request->filled(['origem', 'destino', 'data'])) {
            return redirect()
                ->route('home')
                ->with('error', 'Por favor, preencha origem, destino e data para realizar a busca.');
        }

        // Mock de dados: Dados testes

        $linhas = \App\DTOs\LinhaResultadoDTO::fake();
        return view('buscas.index', [
            'linhas' => $linhas
        ]);
    }
}
