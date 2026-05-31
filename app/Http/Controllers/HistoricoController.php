<?php

// Controller do histórico
// No Laravel, o service usa Eloquent com when()
// Passa $filter->entidade pra view controlar qual aba está ativa.

namespace App\Http\Controllers;

use App\DTOs\HistoricoFilterDTO;
use App\Services\HistoricoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoricoController extends Controller
{
    public function __construct(
        private readonly HistoricoService $historicoService,
    ) {}

    public function index(Request $request): View
    {
        $filter = HistoricoFilterDTO::fromRequest($request);
        $historico = $this->historicoService->getHistory($filter);

        return view('admin.historico.index', [
            'title' => 'Histórico de alterações',
            'historico' => $historico,
            'filter' => $filter,
        ]);
    }
}
