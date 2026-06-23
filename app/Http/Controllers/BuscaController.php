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

        $apiIdOrigem       = (int) $origem->api_id;
        $apiIdDestino      = (int) $destino->api_id;

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
        if (!$request->filled('data') || $request->input('data') < date('Y-m-d')) {
            return redirect()->route('home')->with('error', 'Data inválida ou ausente para visualizar a linha.');
        }

        $linhaDados = $this->transporteService->buscarLinhaPorId($linha);

        // Linha inexistente / API sem retorno → 404
        if (empty($linhaDados)) {
            abort(404);
        }

        $diaSemanaCompleto  = Carbon::parse($request->get('data'))->locale('pt_BR')->translatedFormat('l');
        $diaSemana          = str_replace('-feira', '', $diaSemanaCompleto);

        $horarios           = $this->transporteService->listarHorariosDaLinha($linha);

        // Busca o ID com fallback. Tenta na raiz, depois tenta dentro de 'data', e se não achar usa null.
        $origemId  = $linhaDados['terminal_origem_id'] ?? $linhaDados['data']['terminal_origem_id'] ?? null;
        $destinoId = $linhaDados['terminal_destino_id'] ?? $linhaDados['data']['terminal_destino_id'] ?? null;

        // Só faz a requisição para a API se o ID realmente existir
        $terminalOrigemRaw  = $origemId ? $this->transporteService->buscarTerminal($origemId) : ['data' => []];
        $terminalDestinoRaw = $destinoId ? $this->transporteService->buscarTerminal($destinoId) : ['data' => []];

        $cidadeOrigem       = $this->cidadeService->find((int) $request->get('origem'));
        $cidadeDestino      = $this->cidadeService->find((int) $request->get('destino'));
        $cidades            = $this->cidadeService->all();

        $terminalOrigemDTO  = \App\DTOs\TerminalDTO::fromArray($terminalOrigemRaw['data'] ?? [], $cidadeOrigem);
        $terminalDestinoDTO = \App\DTOs\TerminalDTO::fromArray($terminalDestinoRaw['data'] ?? [], $cidadeDestino);

        // Remove o envelope 'data' caso a API o utilize
        $linhaReal = $linhaDados['data'] ?? $linhaDados;

        $horariosCollection = collect($horarios['data'] ?? [])->map(function (array $horarioRaw) use ($linhaReal) {
            return \App\DTOs\HorarioResultadoDTO::fromArray(
                $horarioRaw,
                (float) ($linhaReal['preco_min'] ?? 0.0),
                isset($linhaReal['preco_max']) ? (float) $linhaReal['preco_max'] : null
            );
        });

        // 1. Pega APENAS o texto (nome) das cidades para o Hero Banner não imprimir JSON na tela
        $nomeOrigem  = $cidadeOrigem ? $cidadeOrigem->nome : 'Origem';
        $nomeDestino = $cidadeDestino ? $cidadeDestino->nome : 'Destino';

        // 2. Converte o array para Objeto (stdClass) para a View conseguir ler $linha->numero
        $linhaObjeto = json_decode(json_encode($linhaReal));

        return view('buscas.show', [
            'linha'           => $linhaObjeto,
            'horarios'        => $horariosCollection,
            'terminalOrigem'  => $terminalOrigemDTO,
            'terminalDestino' => $terminalDestinoDTO,
            'cidades'         => $cidades,
            'origem'          => $nomeOrigem,    // Enviando a variável correta
            'destino'         => $nomeDestino,   // Enviando a variável correta
        ]);
    }
}
