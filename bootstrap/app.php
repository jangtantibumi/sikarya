<?php

use App\Exceptions\BusinessRuleException;
use App\Http\Middleware\CustomerPortalMiddleware;
use App\Http\Middleware\EnforceAbsoluteSessionLifetime;
use App\Http\Middleware\EnsureEmployeeOrAlumniScope;
use App\Http\Middleware\EnsureErpAccessGate;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureMasterDemoAuthenticated;
use App\Http\Middleware\ResolveTenantContext;
use App\Http\Middleware\SecurityHeaders;
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
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'erp.gate' => EnsureErpAccessGate::class,
            'feature' => EnsureFeatureEnabled::class,
            'employee.or.alumni' => EnsureEmployeeOrAlumniScope::class,
            'tenant.context' => ResolveTenantContext::class,
            'master.demo.auth' => EnsureMasterDemoAuthenticated::class,
            'customer.portal' => CustomerPortalMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request): ?string => $request->is('api/*') ? null : '/'
        );

        $middleware->appendToGroup('web', [
            EnforceAbsoluteSessionLifetime::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/whatsapp',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (BusinessRuleException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });
    })->create();
