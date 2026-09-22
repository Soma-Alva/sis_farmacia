<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,

            // ✅ TU MIDDLEWARE DEBE IR AQUÍ (DESPUÉS DE StartSession)
            \App\Http\Middleware\SessionTimeout::class,

            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ],
    ];
    protected $middlewareAliases = [
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];

    protected $routeMiddleware = [
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];
}
