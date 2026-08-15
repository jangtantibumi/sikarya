<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceAbsoluteSessionLifetime
{
    private const MAX_AGE_SECONDS = 7 * 24 * 60 * 60;

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        if (!$request->user()?->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson() || $request->is('api/*')
                ? response()->json([
                    'message' => 'Akun Anda sudah dinonaktifkan.',
                    'code' => 'ACCOUNT_INACTIVE',
                ], 401)
                : redirect('/');
        }

        $startedAt = (int) $request->session()->get('absolute_login_at', 0);

        if ($startedAt === 0) {
            $request->session()->put('absolute_login_at', now()->timestamp);

            return $next($request);
        }

        if (now()->timestamp - $startedAt < self::MAX_AGE_SECONDS) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Sesi tujuh hari telah berakhir. Silakan masuk kembali.',
                'code' => 'SESSION_EXPIRED',
            ], 401);
        }

        return redirect('/')->with('session_expired', true);
    }
}
