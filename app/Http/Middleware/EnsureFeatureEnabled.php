<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\CompanyFeatureManager;
use App\Services\FeatureManager;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function __construct(private readonly FeatureManager $features) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenantId = app(TenantContext::class)->id();
        $tenantFeature = $this->tenantFeatureFor($feature);

        if (! $tenantId || ! $tenantFeature) {
            if (! $this->features->enabled($feature)) {
                return $this->disabled($feature, 'Modul ini sedang dinonaktifkan oleh Superadmin.', 'FEATURE_DISABLED');
            }

            return $next($request);
        }

        $company = Company::query()->findOrFail($tenantId);
        $catalogue = app(CompanyFeatureManager::class)->catalogue($company);
        $state = collect($catalogue)->firstWhere('key', $tenantFeature)['state'] ?? 'off';

        if ($state === 'off') {
            return $this->disabled($feature, 'Modul ini dinonaktifkan untuk perusahaan Anda.', 'TENANT_FEATURE_DISABLED');
        }

        if ($state === 'read_only' && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $this->disabled($feature, 'Modul ini berada dalam mode hanya-baca untuk perusahaan Anda.', 'TENANT_FEATURE_READ_ONLY');
        }

        return $next($request);
    }

    private function tenantFeatureFor(string $feature): ?string
    {
        return match ($feature) {
            'organization', 'performance', 'approvals', 'attendance', 'leave', 'resignation', 'hr_core', 'chat', 'notifications', 'backup', 'talent_management' => 'people',
            'crm' => 'crm',
            'document_management' => 'documents',
            'accounting', 'finance' => 'accounting',
            'project_costing' => 'project_costing',
            'payroll' => 'payroll',
            'client_portal' => 'client_portal',
            'advanced_analytics', 'gemini' => 'intelligence',
            'inventory' => 'inventory',
            'procurement' => 'purchasing',
            'production' => 'production',
            'pos' => 'pos',
            default => null,
        };
    }

    private function disabled(string $feature, string $message, string $code): Response
    {
        return response()->json([
            'message' => $message,
            'code' => $code,
            'feature' => $feature,
        ], 403);
    }
}
