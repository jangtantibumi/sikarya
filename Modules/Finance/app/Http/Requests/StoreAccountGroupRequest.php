<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'category' => 'required|in:asset,liability,equity,revenue,expense',
            'code_from' => 'nullable|string|max:50',
            'code_to' => 'nullable|string|max:50',
            'report_type' => 'required|in:balance_sheet,profit_loss',
            'is_active' => 'boolean',
        ];
    }
}
