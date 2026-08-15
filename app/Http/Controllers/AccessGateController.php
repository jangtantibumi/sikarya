<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureErpAccessGate;
use App\Services\SecurityAuditService;
use App\Services\SecuritySettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessGateController extends Controller
{
    public function __construct(
        private readonly SecuritySettingsService $settings,
        private readonly SecurityAuditService $audit,
    ) {
    }

    public function show(): View|RedirectResponse
    {
        if (!$this->settings->gateEnabled()) {
            return redirect('/');
        }

        return view('access-gate', [
            'sessionHours' => (int) $this->settings->get('security.gate.session_hours', 12),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_password' => ['required', 'string', 'max:255'],
        ]);

        if (!$this->settings->gateEnabled() || !$this->settings->verifyGatePassword($validated['access_password'])) {
            $this->audit->record('access_gate.failed', request: $request);

            return back()
                ->withErrors(['access_password' => 'Password akses perusahaan tidak sesuai.'])
                ->withInput();
        }

        $request->session()->regenerate();
        EnsureErpAccessGate::grant($request);
        $this->audit->record('access_gate.granted', request: $request);

        return redirect()->intended('/');
    }
}
