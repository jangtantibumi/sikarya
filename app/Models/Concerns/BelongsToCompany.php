<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** @mixin Model */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('active_company', function (Builder $query): void {
            $companyId = app(TenantContext::class)->id();

            if ($companyId !== null) {
                $query->where($query->getModel()->qualifyColumn('company_id'), $companyId);
            }
        });

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);

            if ($context->active() && ! $model->getAttribute('company_id')) {
                $model->setAttribute('company_id', $context->id());
            }
        });
    }
}
