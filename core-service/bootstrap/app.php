<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configurer Sanctum pour les requêtes API stateless
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gérer les exceptions d'authentification
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // Pour toutes les requêtes API, retourner du JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié. Token manquant ou invalide.'
                ], 401);
            }
            
            // Fallback (ne devrait pas arriver)
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        });
    })
    ->create();
