<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\TutorMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    // CSRF EXCLUSIONS - Weka hapa!
        $middleware->validateCsrfTokens(except: [
            'webhooks/nmb/payment', // Exclude webhook route
            'api/webhooks/*',       // Exclude all api webhooks
            'mpesa/stk-push/*',
            'payments/webhook/*'    // Au hii kama unatumia
        ]);

        // REGISTER MIDDLEWARE ALIASES (Laravel 11 replacement for Kernel)
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'force.password.change' => ForcePasswordChange::class,
            'tutor' => TutorMiddleware::class,
            'user_type' => CheckUserType::class,
             'applicant' => ApplicantOnly::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
