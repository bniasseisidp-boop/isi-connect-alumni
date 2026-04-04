<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <-- Requis
use Illuminate\Auth\AuthenticationException; // <-- Requis pour la gestion d'erreur

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // On laisse ce bloc vide, comme à l'origine
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // --- C'EST LE BLOC CORRECT À AJOUTER ---
        // On gère les échecs d'authentification pour l'API
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            // Si la requête commence par 'api/'
            if ($request->is('api/*')) { 
                return response()->json(
                    ['message' => 'Non authentifié.'], 
                    401 // Le code "Non Autorisé"
                );
            }
        });
        // --- FIN DU BLOC À AJOUTER ---

    })->create();