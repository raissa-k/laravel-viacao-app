<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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
                ->get($url . '/api/linhas', [
                    'origem_cidade_id'  => $origemApiId,
                    'destino_cidade_id' => $destinoApiId,
                    'page'              => $pagina,
                    'per_page'          => $perPage,
                ]);

            if ($response->failed()) {
                Log::error('TransporteService: falha ao listar linhas', [
                    'status'    => $response->status(),
                    'origemId'  => $origemApiId,
                    'destinoId' => $destinoApiId,
                    'pagina'    => $pagina,
                ]);

                return ['data' => [], 'meta' => []];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('TransporteService: exceção ao listar linhas', [
                'erro'      => $e->getMessage(),
                'origemId'  => $origemApiId,
                'destinoId' => $destinoApiId,
                'pagina'    => $pagina,
            ]);

            return ['data' => [], 'meta' => []];
        }
    }

    public function listarTodasLinhas(int $origemApiId, int $destinoApiId): array
    {
        $resultado = $this->listarLinhas($origemApiId, $destinoApiId);
        $todos     = $resultado['data'];
        $lastPage  = $resultado['meta']['last_page'] ?? 1;

        for ($pagina = 2; $pagina <= $lastPage; $pagina++) {
            $resultado = $this->listarLinhas($origemApiId, $destinoApiId, $pagina);
            $todos     = array_merge($todos, $resultado['data']);
        }

        return $todos;
    }

    public function buscarTerminal(int $id): array
    {
        $chave     = "terminal:{$id}";

        if (Cache::has($chave)) {
            Log::debug('TransporteService: cache hit ao buscar terminal por ID', [
                'id'    => $id,
                'chave' => $chave,
            ]);

            return Cache::get($chave);
        }

        $resultado = $this->buscarTerminalSemCache($id);

        // só cacheia se veio dado de verdade — erro/vazio não grudam por 1h
        if (!empty($resultado['data'])) {
            Cache::put($chave, $resultado, now()->addHour());
        }

        return $resultado;
    }

    public function buscarTerminalSemCache(int $id): array
    {
        try {
            $url      = config('services.transporte_api.url'); //para prevenir barra dupla
            $response = Http::withToken($this->gerarToken())   //geração do token para acesso
            ->get($url . '/api/terminais/' . $id);         //pegando as infos do terminal

            if ($response->failed()) {
                Log::error('TransporteService: falha ao buscar terminal por ID', [
                    'status' => $response->status(),
                    'id'     => $id,
                ]);

                return ['data' => []];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('TransporteService: exceção ao buscar terminal por ID', [
                'erro' => $e->getMessage(),
                'id'   => $id,
            ]);

            return ['data' => []];
        }
    }

    public function listarOperadoras(int $pagina, int $perPage): array
    {
        try {
            $url      = config('services.transporte_api.url');
            $response = Http::withToken($this->gerarToken())
                ->get($url . '/api/operadoras', [
                    'page'     => $pagina,
                    'per_page' => $perPage,
                ]);

            if ($response->failed()) {
                Log::error('TransporteService: falha ao listar operadoras', [
                    'status' => $response->status(),
                    'pagina' => $pagina,
                ]);

                return ['data' => [], 'meta' => []];

            }

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('TransporteService: exceção ao listar operadoras', [
                'erro'   => $e->getMessage(),
                'pagina' => $pagina,
            ]);

            return ['data' => [], 'meta' => []];
        }
    }

    public function listarTodasOperadoras(): array
    {
        $resultado = $this->listarOperadoras(1, 50);

        $todos     = is_array($resultado['data']) ? $resultado['data'] : [];
        $lastPage  = $resultado['meta']['last_page'] ?? 1;
        for ($pagina = 2; $pagina <= $lastPage; $pagina++) {
            $resultado = $this->listarOperadoras($pagina, 50);

            if (!empty($resultado['data']) && is_array($resultado['data'])) {
                $todos = array_merge($todos, $resultado['data']);
            }
        }

        return $todos;
    }

    public function buscarLinhaPorId(int $id): array
    {
        try {
            $url      = config('services.transporte_api.url');
            $response = Http::withToken($this->gerarToken())
                ->get($url.'/api/linhas/'.$id);

            if ($response->failed()) {
                Log::error('TransporteService: falha ao buscar linha por ID', [
                    'status' => $response->status(), // Registrará 404 se não existir
                    'id'     => $id,
                ]);

                // Fallback documentado para "não existe" ou erro na requisição
                return [];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('TransporteService: exceção ao buscar linha por ID', [
                'erro' => $e->getMessage(),
                'id'   => $id,
            ]);

            return [];
        }
    }

    public function listarHorariosDaLinha(int $id): array
    {
        try {
            $url      = config('services.transporte_api.url');
            $response = Http::withToken($this->gerarToken())
                ->get($url.'/api/linhas/'.$id.'/horarios');

            if ($response->failed()) {
                Log::error('TransporteService: falha ao listar horarios da linha', [
                    'status' => $response->status(),
                    'id'     => $id,
                ]);

                return [];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('TransporteService: exceção ao listar horarios da linha', [
                'erro' => $e->getMessage(),
                'id'   => $id,
            ]);

            return [];
        }
    }
}
