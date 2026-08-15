<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\User;

/** Holds the active company for one HTTP request or job execution. */
class TenantContext
{
    private ?int $companyId = null;

    public function setCompany(Company|int|null $company): void
    {
        $this->companyId = $company instanceof Company ? $company->id : $company;
    }

    public function setUser(?User $user): void
    {
        $this->companyId = $user?->company_id ? (int) $user->company_id : null;
    }

    public function id(): ?int
    {
        return $this->companyId;
    }

    public function active(): bool
    {
        return $this->companyId !== null;
    }

    public function clear(): void
    {
        $this->companyId = null;
    }
}
