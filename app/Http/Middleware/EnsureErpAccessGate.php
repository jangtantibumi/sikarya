<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SecuritySettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureErpAccessGate
{
    private const SESSION_KEY = 'erp_gate_granted_at';

    public function __construct(private readonly SecuritySettingsService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->settings->gateEnabled()) {
            return $next($request);
        }

        $grantedAt = (int) $request->session()->get(self::SESSION_KEY, 0);
        $validForSeconds = max(1, (int) $this->settings->get('security.gate.session_hours', 12)) * 3600;

        if ($grantedAt > 0 && now()->timestamp - $grantedAt < $validForSeconds) {
            return $next($request);
        }

        $request->session()->forget(self::SESSION_KEY);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Masukkan password akses perusahaan untuk melanjutkan.',
                'code' => 'ERP_GATE_REQUIRED',
            ], 423);
        }

        return redirect()->route('erp-access.show');
    }

    public static function grant(Request $request): void
    {
        $request->session()->put(self::SESSION_KEY, now()->timestamp);
    }
}
