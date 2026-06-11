<?php

declare(strict_types=1);

// Controller da home pública: carrega só viações ativas.

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Services\ViacaoService;
use Illuminate\View\View;
use App\Services\CidadeService;

class HomeController extends Controller
{
    public function __construct(
        private readonly ViacaoService $viacaoService,
    ) {
    }

    public function index( CidadeService $cidadeService): View
    {
        $viacoes = $this->viacaoService->active();
        $cidades = $cidadeService->all();

        return view('home.index', [
            'title'   => 'Quero Passagem',
            'viacoes' => $viacoes,
            'cidades' => $cidades,
        ]);
    }
}
