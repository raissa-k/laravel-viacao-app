<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransporteService
{
    private function gerarToken(): string
    {
        $tokenBase = config('services.transporte_api.token');
        $data      = now()->setTimezone('America/Sao_Paulo')->toDateString();

        return hash('sha256', $tokenBase . ':' . $data);
    }

    public function listarCidades(int $pagina, int $perPage): array
    {
        try {
            $url      = config('services.transporte_api.url');
            $response = Http::withToken($this->gerarToken())
                ->get($url . '/api/cidades', [
                    'page'     => $pagina,
                    'per_page' => $perPage,
                ]);

            if ($response->failed()) {
                Log::error('TransporteService: falha ao listar cidades', [
                    'status' => $response->status(),
                    'pagina' => $pagina,
                ]);

                return ['data' => [], 'meta' => []];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('TransporteService: exceção ao listar cidades', [
                'erro'   => $e->getMessage(),
                'pagina' => $pagina,
            ]);

            return ['data' => [], 'meta' => []];
        }
    }

    public function listarTodasCidades(): array
    {
        $resultado = $this->listarCidades(1, 50);
        $todos     = $resultado['data'];
        $lastPage  = $resultado['meta']['last_page'] ?? 1;

        for ($pagina = 2; $pagina <= $lastPage; $pagina++) {
            $resultado = $this->listarCidades($pagina, 50);
            $todos     = array_merge($todos, $resultado['data']);
        }

        return $todos;
    }

    public function listarLinhas(int $origemApiId, int $destinoApiId, int $pagina = 1, int $perPage = 50): array
    {
        try {
        $url      = config('services.transporte_api.url');
        $response = Http::withToken($this->gerarToken())
            ->get($url . '/api/listar', [
                'origemApiId' => $origemApiId,
                'destinoApiId' => $destinoApiId,
                'pagina' => $pagina,
                'per_page' => $perPage,
            ]);

        if ($response->failed()) {
            Log::error('TransporteService: falha ao listar linhas', [
                'status' => $response->status(),
                'pagina' => $pagina,
            ]);

            return ['data' => [], 'meta' => []];
        }

        return $response->json();
    } catch (\Throwable $e) {
        Log::error('TransporteService: exceção ao listar linhas', [
            'erro'   => $e->getMessage(),
            'pagina' => $pagina,
        ]);

        return ['data' => [], 'meta' => []];
    }

    }

    public function listarTodasLinhas(int $pagina = 1, int $perPage = 50): array
    {
        $resultado = $this->listarLinhas($pagina, $perPage);
        $todos     = $resultado['data'];
        $lastPage  = $resultado['meta']['last_page'] ?? 1;

        for ($pagina = 2; $pagina <= $lastPage; $pagina++) {
            $resultado = $this->listarLinhas($pagina, $perPage);
            $todos     = array_merge($todos, $resultado['data']);
        }

        return $todos;

    }

}
