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

        //-- bloco de condição para caso a data seja no passado --
        if ($request->input('data') < date('Y-m-d')) {
            return redirect()
                ->route('home')
                ->with('error', 'A data da busca não pode ser uma data no passado.');
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
        $diaSemanaCompleto = Carbon::parse($request->get('data'))->locale('pt_BR')->translatedFormat('l');
        $diaSemana         = str_replace('-feira', '', $diaSemanaCompleto);

        $linhasFiltradas   = $this->filtrarLinhas->execute($linhasBrutas, null, $diaSemana);

        return view('buscas.index', [
            'linhas'  => $linhasFiltradas,
            'origem'  => $origem,
            'destino' => $destino,
            'cidades' => $cidades,
        ]);
    }

    public function show(Request $request, int $linha): View|RedirectResponse
    {
        // valida o parâmetro 'data'
        if (!$request->filled('data') || $request->input('data') < date('Y-m-d')) {
            return redirect()
                ->route('home')
                ->with('error', 'Data inválida ou ausente para visualizar a linha.');
        }

        // busca a linha na API; se falhar/não existir, 404
        $linhaDados        = $this->transporteService->buscarLinhaPorId($linha);

        if (empty($linhaDados)) {
            abort(404);
        }

        // dia da semana derivado da data, repassado como filtro
        $diaSemanaCompleto = Carbon::parse($request->get('data'))->locale('pt_BR')->translatedFormat('l');
        $diaSemana         = str_replace('-feira', '', $diaSemanaCompleto);

        $horarios          = $this->transporteService->listarHorariosDaLinha($linha, $diaSemana);

        // terminais de origem e destino via método COM cache
        $terminalOrigem    = $this->transporteService->buscarTerminal($linhaDados['terminal_origem_id']);
        $terminalDestino   = $this->transporteService->buscarTerminal($linhaDados['terminal_destino_id']);

        $cidades           = $this->cidadeService->all();

        // passa as variáveis cruas para a view
        return view('buscas.show', [
            'linha'           => $linhaDados,
            'horarios'        => $horarios,
            'terminalOrigem'  => $terminalOrigem,
            'terminalDestino' => $terminalDestino,
            'cidades'         => $cidades,
        ]);
    }
}
