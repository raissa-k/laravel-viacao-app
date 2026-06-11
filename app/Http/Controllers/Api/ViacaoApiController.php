<?php

declare(strict_types=1);

// - response()->json() substitui header() + json_encode() + echo
// - Autenticação delegada ao middleware auth:sanctum
// (rotas protegidas não precisam mais verificar token manualmente porque o framework já rejeitou a request antes de chegar aqui)
// - auth()->id() substitui $this->auth->userId() do PHP puro
//
// No Laravel:  middleware auth:sanctum verifica o Bearer token uma vez, antes do controller. Se inválido, retorna 401 automaticamente.
// Pesquise "Laravel Sanctum API tokens", "middleware pipeline Laravel".

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ViacaoApiRequest;
use App\Http\Resources\ViacaoResource;
use App\Services\ViacaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ViacaoApiController extends Controller
{
    public function __construct(
        private readonly ViacaoService $viacaoService,
    ) {
    }

    // Rotas públicas (sem autenticação)

    /** Lista todas as viações ativas. */
    public function index(): JsonResponse
    {
        /*
         * SEGURANÇA: usando active() e não all() aqui.
         *
         * ViacaoService::all(new ViacaoFilterDTO) retorna TODAS as viações, incluindo as inativas (ativa = false).
         * Útil no painel admin onde o operador precisa ver e gerenciar todas as viações, mas este endpoint é PÚBLICO (sem auth:sanctum).
         * Viações inativas são intencionalmente escondidas da home pública porque o operador as desativou.
         *
         * Regra geral: endpoints públicos devem expor apenas dados que a interface pública também expõe.
         * Se a home só mostra ativas, a API pública também só deve mostrar ativas.
         *
         * Pesquise: "principle of least privilege", "API data exposure", "broken access control".
         */
        $viacoes = $this->viacaoService->active();

        /*
         * ViacaoResource::collection() transforma cada viação no array definido em toArray().
         * Compare com PHP puro, algo como: json_encode(array_map(fn($v) => $v->toArray(), $viacoes))
         *
         * Benefícios:
         * - Separação: model (persistência) vs resource (shape JSON)
         * - Segurança: controla exatamente quais campos retorna
         * - Evolução: se o schema mudar, o JSON fica igual
         */
        return response()->json([
            'ok'    => true,
            'count' => $viacoes->count(),
            'data'  => ViacaoResource::collection($viacoes),
        ]);
    }

    /** Retorna uma viação pelo ID. */
    public function show(int $id): JsonResponse
    {
        $viacao = $this->viacaoService->find($id);

        if ($viacao === null) {
            return response()->json(['ok' => false, 'message' => 'Viação não encontrada.'], 404);
        }

        return response()->json(['ok' => true, 'data' => new ViacaoResource($viacao)]);
    }

    // Rotas protegidas (middleware auth:sanctum garante usuário autenticado)

    /**
     * Cria uma nova viação.
     *
     * Store() e update() tinham as mesmas regras duplicadas, agora há uma única fonte de verdade.
     * Se a validação falhar, o Laravel devolve 422 Unprocessable Entity automaticamente com os erros em JSON.
     * Pesquise "Laravel FormRequest API validation", "HTTP 422".
     */
    public function store(ViacaoApiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $viacao    = $this->viacaoService->create(
            $validated['nome'],
            $validated['cidade_id'],
            (bool) ($validated['ativa'] ?? true),
            null, // uploads via API não são suportados nesse demo
            null,
            auth()->id(),
        );

        // 201 Created: retorna o recurso completo (via Resource) pro cliente não precisar de um GET extra.
        return response()->json(['ok' => true, 'data' => new ViacaoResource($viacao)], 201);
    }

    /** Atualiza uma viação. */
    public function update(ViacaoApiRequest $request, int $id): JsonResponse
    {
        $viacao    = $this->viacaoService->find($id);

        if ($viacao === null) {
            return response()->json(['ok' => false, 'message' => 'Viação não encontrada.'], 404);
        }

        $validated = $request->validated();

        $viacao    = $this->viacaoService->update(
            $viacao,
            $validated['nome'] ?? $viacao->nome,
            isset($validated['cidade_id']) ? (int) $validated['cidade_id'] : $viacao->cidade_id,
            (bool) ($validated['ativa'] ?? $viacao->ativa),
            $viacao->logo,
            null,
            auth()->id(),
        );

        return response()->json(['ok' => true, 'data' => new ViacaoResource($viacao)]);
    }

    /** Remove uma viação. */
    public function destroy(int $id): JsonResponse|Response
    {
        $viacao = $this->viacaoService->find($id);

        if ($viacao === null) {
            return response()->json(['ok' => false, 'message' => 'Viação não encontrada.'], 404);
        }

        $this->authorize('delete', $viacao);

        // 204 No Content: deletar bem-sucedido sem retornar corpo.
        $this->viacaoService->delete($viacao, auth()->id());

        return response()->noContent();
    }
}
