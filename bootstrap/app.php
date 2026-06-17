<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.optional' => \App\Http\Middleware\AttemptAuthentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 422 — Validação de Form Request
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'message' => 'Os dados fornecidos são inválidos.',
                'errors' => $e->errors(),
            ], 422);
        });

        // 401 — Não autenticado
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'message' => 'Não autenticado.',
            ], 401);
        });

        // 403 — Autenticado, mas sem permissão (Policy)
        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'message' => 'Você não tem permissão para executar esta ação.',
            ], 403);
        });

        // 404 — Model não encontrado
        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Recurso não encontrado.',
            ], 404);
        });

        // 404 — Rota inexistente
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'message' => 'A URL informada não foi encontrada ou possui formato inválido.',
            ], 404);
        });

        // 405 — Método HTTP não permitido
        $exceptions->render(function (MethodNotAllowedHttpException $e) {
            return response()->json([
                'message' => 'Método HTTP não permitido para este endpoint.',
            ], 405);
        });

        // 429 — Rate limit excedido
        $exceptions->render(function (TooManyRequestsHttpException $e) {
            return response()->json([
                'message' => 'Limite de requisições excedido. Tente novamente em breve.',
            ], 429);
        });

        // 401 — Token JWT expirado
        $exceptions->render(function (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token expirado.',
            ], 401);
        });

        // 401 — Token JWT inválido
        $exceptions->render(function (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token inválido.',
            ], 401);
        });

        // 401 — Token JWT ausente
        $exceptions->render(function (JWTException $e) {
            return response()->json([
                'message' => 'Token de autenticação ausente.',
            ], 401);
        });

        // 500 — Fallback genérico
        $exceptions->render(function (\Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Erro na requisição.',
                ], $e->getStatusCode());
            }

            return response()->json([
                'message' => 'Erro interno do servidor.',
            ], 500);
        });
    })->create();
