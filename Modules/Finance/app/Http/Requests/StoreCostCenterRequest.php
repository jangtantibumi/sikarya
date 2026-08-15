<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'nullable|uuid',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|uuid|exists:finance_cost_centers,id',
            'manager_name' => 'nullable|string|max:150',
            'department' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}
