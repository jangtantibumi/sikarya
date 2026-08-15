<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        $context->setUser($request->user());

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
