<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerPortalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('customer_portal_id')) {
            return redirect()->route('portal.login');
        }

        return $next($request);
    }
}
