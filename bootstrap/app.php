<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'custom.auth' => \App\Http\Middleware\CustomAuth::class,
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'check.verification' => \App\Http\Middleware\CheckVerificationStatus::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Precompute dashboard metrics every 10 minutes
        $schedule->command('dashboard:precompute')->everyTenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
