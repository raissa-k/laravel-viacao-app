<?php

declare(strict_types=1);

// Controller da home pública: carrega só viações ativas.

namespace App\Http\Controllers;

use App\Services\ViacaoService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly ViacaoService $viacaoService,
    ) {
    }

    public function index(): View
    {
        $viacoes = $this->viacaoService->active();

        return view('home.index', [
            'title'   => 'Quero Passagem',
            'viacoes' => $viacoes,
        ]);
    }
}
