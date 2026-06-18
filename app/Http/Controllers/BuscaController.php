<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FiltrarLinhas;
use App\Services\CidadeService;
use App\Services\TransporteService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuscaController extends Controller
{
    public function __construct(
        private readonly CidadeService $cidadeService,
        private readonly TransporteService $transporteService,
        private readonly FiltrarLinhas $filtrarLinhas
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->filled(['origem','destino','data'])) {
            return redirect()
                ->route('home')
                ->with('error', 'Por favor, preencha origem, destino e data para realizar a busca.');
        }

        $origem            = $this->cidadeService->find((int) $request->get('origem'));
        $destino           = $this->cidadeService->find((int) $request->get('destino'));
        $cidades           = $this->cidadeService->all();

        if (!$origem || !$destino) {
            return redirect()
                ->route('home')
                ->with('error', 'Cidade não encontrada.');
        }

        $apiIdOrigem       = $origem->api_id;
        $apiIdDestino      = $destino->api_id;

        $linhasBrutas      = $this->transporteService->listarTodasLinhas($apiIdOrigem, $apiIdDestino);
        Carbon::setLocale('pt_BR');
        $diaSemanaCompleto = Carbon::parse($request->get('data'))->translatedFormat('l');
        $diaSemana         = str_replace('-feira', '', $diaSemanaCompleto);

        $linhasFiltradas   = $this->filtrarLinhas->execute($linhasBrutas, null, $diaSemana);

        return view('buscas.index', [
            'linhas'  => collect($linhasFiltradas),
            'origem'  => $origem,
            'destino' => $destino,
            'cidades' => $cidades,
        ]);
    }
}
