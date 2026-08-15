<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_group_id' => 'nullable|uuid|exists:finance_account_groups,id',
            'code' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:200',
            'type' => 'sometimes|required|in:asset,liability,equity,revenue,expense',
            'balance_type' => 'sometimes|required|in:debit,credit',
            'parent_id' => 'nullable|uuid|exists:finance_chart_of_accounts,id',
            'currency_id' => 'nullable|uuid|exists:finance_currencies,id',
            'is_header' => 'boolean',
            'is_reconciliation' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
