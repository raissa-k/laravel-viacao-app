<?php

declare(strict_types=1);

// Controller da home pública: carrega só viações ativas.

namespace App\Http\Controllers;

use App\Services\CidadeService;
use App\Services\ViacaoService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly ViacaoService $viacaoService,
        private readonly CidadeService $cidadeService,
    ) {
    }

    public function index(): View
    {
        $viacoes = $this->viacaoService->active();
        $cidades = $this->cidadeService->all();


        return view('home.index', [
            'title'   => 'Quero Passagem',
            'viacoes' => $viacoes,
            'cidades' => $cidades,
        ]);
    }
}
