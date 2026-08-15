<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNumberingSeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'nullable|uuid',
            'module_code' => 'sometimes|required|string|max:50',
            'document_type' => 'sometimes|required|string|max:50',
            'prefix' => 'sometimes|required|string|max:50',
            'suffix' => 'nullable|string|max:50',
            'length' => 'integer|min:1|max:10',
            'current_number' => 'integer|min:0',
            'reset_cycle' => 'sometimes|required|in:never,yearly,monthly,daily',
            'is_active' => 'boolean',
        ];
    }
}
