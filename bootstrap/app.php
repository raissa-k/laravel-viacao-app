<?php

declare(strict_types=1);

use App\Models\Cidade;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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

            if ($isApi($request)) {
                return response()->json([
                    'error' => 'Página não encontrada.',
                    'code'  => 404,
                ], 404);
            }

            try {
                $cidades = Cidade::all();
            } catch (Throwable) {
                $cidades = collect();
            }

            return response()->view('errors.404', [
                'cidades' => $cidades,
            ], 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'error' => 'Método não permitido.',
                    'code'  => 405,
                ], 405);
            }
        });

        /*
             * ModelNotFoundException é lançada pelo route model binding quando o model não é encontrado.
             * Exemplo: Route::get('/viacoes/{viacao}', ...) com ID inexistente -> ModelNotFoundException.
             * O ViacaoApiController atual usa int $id + find() manual e retorna JSON diretamente,
             * aqui só entra em ação se as rotas de API passarem a usar route model binding.
             * A mensagem é genérica ("Recurso") para funcionar com qualquer model.
             */

        $exceptions->render(function (Throwable $e, Request $request) use ($isApi) {

            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;


            if ($isApi($request)) {
                $message = $statusCode === 500 ? 'Erro Interno no Servidor' : $e->getMessage();
                return response()->json([
                    'error' => $message,
                    'code'  => $statusCode,
                ], $statusCode);
            }

            if ($statusCode === 500) {
                $traceId = (string) Str::uuid();

                Log::error("Erro interno no servidor [Trace ID: {$traceId}]: " . $e->getMessage(), [
                    'exception' => $e,
                    'url'       => $request->fullUrl()
                ]);

                return response()->view('errors.500', ['traceId' => $traceId], 500);
            }
        });

    })->create();
