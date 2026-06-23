<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
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

        // Helper para identificar requisições de API de forma robusta
        $isApi = fn (Request $request): bool => $request->is('api', 'api/*') || $request->expectsJson();

        // 1. Tratamento específico para 404 (Geral, Route Model Binding ou rotas inexistentes)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'error' => 'Página não encontrada.', // Exatamente a string cobrada no teste
                    'code' => 404,
                ], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // 2. Tratamento para métodos HTTP incorretos (ex: dar POST onde deveria ser GET)
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'error' => 'Método não permitido.',
                    'code' => 405,
                ], 405);
            }
        });

        // 3. Tratamento genérico para erros fatais (Erros 500 / Exceções não capturadas)
        $exceptions->render(function (Throwable $e, Request $request) use ($isApi) {

            // Determina se é um erro HTTP ou um erro de código puro (500)
            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            // Se for API, devolve a estrutura JSON unificada requerida
            if ($isApi($request)) {
                $message = $statusCode === 500 ? 'Erro Interno no Servidor' : $e->getMessage();
                return response()->json([
                    'error' => $message,
                    'code' => $statusCode,
                ], $statusCode);
            }

            // Se for WEB e for um erro crítico do servidor (500)
            if ($statusCode === 500) {
                $traceId = (string) Str::uuid();

                // Registra o ID no log com espaço corrigido conforme boas práticas
                Log::error("Erro interno no servidor [Trace ID: {$traceId}]: " . $e->getMessage(), [
                    'exception' => $e,
                    'url' => $request->fullUrl()
                ]);

                return response()->view('errors.500', ['traceId' => $traceId], 500);
            }
        });

    })->create();
