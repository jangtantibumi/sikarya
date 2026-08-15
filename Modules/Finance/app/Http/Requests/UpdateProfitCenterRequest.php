<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfitCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'nullable|uuid',
            'code' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:150',
            'parent_id' => 'nullable|uuid|exists:finance_profit_centers,id',
            'manager_name' => 'nullable|string|max:150',
            'segment' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}
