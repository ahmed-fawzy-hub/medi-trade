<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedHttpException $e, $request) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage(),
            ], 401);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        });

        $exceptions->render(function (RouteNotFoundException $e, $request) {
            return response()->json([
                'message' => 'Route not found',
                'error' => $e->getMessage(),
            ], 404);
        });
    })->create();

