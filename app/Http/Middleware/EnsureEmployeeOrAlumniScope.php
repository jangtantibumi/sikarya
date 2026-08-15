<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeOrAlumniScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->isAlumni()) {
            return $next($request);
        }

        $allowed = $request->is(
            'api/alumni',
            'api/alumni/*',
            'api/logout',
            'api/notifications',
            'api/notifications/*',
            'api/backup',
        );

        abort_unless(
            $allowed,
            403,
            'Akun alumni hanya dapat mengakses portal alumni, notifikasi, dan backup data pribadi.',
        );

        return $next($request);
    }
}
