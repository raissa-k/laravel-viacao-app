<?php

// No PHP puro: rotas de API eram registradas no mesmo router com prefixo /api.
// No Laravel: routes/api.php é carregado com prefixo /api automaticamente (bootstrap/app.php).
// Então Route::get('/viacoes') aqui fica disponível como GET /api/viacoes.
//
// Autenticação:
// Middleware auth:sanctum
// ^ o framework verifica o Bearer token na tabela personal_access_tokens e injeta o usuário autenticado via auth()->user().
// Pesquise "Laravel Sanctum API tokens", "Bearer token authentication".
//
// No routes/web.php:
//   Route::get('/viacoes/{viacao}', [...]) >>> Laravel injeta $viacao automaticamente
// Nas rotas de API aqui:
//   Route::get('/viacoes/{id}', [...]) >>> o controller faz $viacao = $this->viacaoService->find($id)
// Se as duas funcionam, por que diferentes?
// - Route binding é mais conciso e comum onde queremos 404 automático
// - find() é mais explícito e comum onde queremos controlar a resposta 404
// Se quiser usar binding na API também:
//   Route::get('/viacoes/{viacao}', ...) + public function show(Viacao $viacao)
//   Pesquise "Laravel Route Model Binding", "implicit bindings", "implicit vs explicit bindings".

use App\Http\Controllers\Api\ViacaoApiController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (sem autenticação)
//
// Paridade com o projeto PHP puro:
// - lá existia GET /api como atalho de GET /api/viacoes
// - aqui reproduzimos o mesmo contrato no mesmo endpoint
//
// Rate limit de leitura:
// - middleware throttle:api-read definido em AppServiceProvider
// - objetivo: reduzir abuso de scraping/flood sem prejudicar uso normal
Route::middleware('throttle:api-read')->group(function () {
    Route::get('/', [ViacaoApiController::class, 'index']); // /api
    Route::get('/viacoes', [ViacaoApiController::class, 'index']);
    Route::get('/viacoes/{id}', [ViacaoApiController::class, 'show']);
});

// Rotas protegidas: exigem Bearer token válido emitido pelo Sanctum.
// O middleware auth:sanctum retorna 401 automaticamente se o token for inválido ou ausente.
// No PHP puro, esse 401 era retornado manualmente dentro de cada method do controller.
//
// Rate limit de escrita:
// - mais restritivo que leitura porque escrita altera estado do sistema
// - ajuda a mitigar abuso e brute force em endpoints de criação/edição/exclusão
Route::middleware(['auth:sanctum', 'throttle:api-write'])->group(function () {
    Route::post('/viacoes', [ViacaoApiController::class, 'store']);
    Route::put('/viacoes/{id}', [ViacaoApiController::class, 'update']);
    Route::delete('/viacoes/{id}', [ViacaoApiController::class, 'destroy']);
});
