<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApi = fn (Request $request): bool => $request->is('api', 'api/*') || $request->expectsJson();

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApi) {
            if (! $isApi($request)) {
                return null;
            }

            /*
             * ModelNotFoundException é lançada pelo route model binding quando o model não é encontrado.
             * Exemplo: Route::get('/viacoes/{viacao}', ...) com ID inexistente -> ModelNotFoundException.
             * O ViacaoApiController atual usa int $id + find() manual e retorna JSON diretamente,
             * aqui só entra em ação se as rotas de API passarem a usar route model binding.
             * A mensagem é genérica ("Recurso") para funcionar com qualquer model.
             */
            $isModelMiss = $e->getPrevious() instanceof ModelNotFoundException;

            return response()->json([
                'ok' => false,
                'message' => $isModelMiss ? 'Recurso não encontrado.' : 'Rota não encontrada.',
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Método não permitido.',
                ], 405);
            }
        });
    })->create();
