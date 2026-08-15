<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        $middleware->alias([
            'erp.gate' => \App\Http\Middleware\EnsureErpAccessGate::class,
            'feature' => \App\Http\Middleware\EnsureFeatureEnabled::class,
            'employee.or.alumni' => \App\Http\Middleware\EnsureEmployeeOrAlumniScope::class,
            'tenant.context' => \App\Http\Middleware\ResolveTenantContext::class,
            'master.demo.auth' => \App\Http\Middleware\EnsureMasterDemoAuthenticated::class,
            'customer.portal' => \App\Http\Middleware\CustomerPortalMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request): ?string => $request->is('api/*') ? null : '/'
        );

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnforceAbsoluteSessionLifetime::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/whatsapp',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (\App\Exceptions\BusinessRuleException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });
    })->create();
