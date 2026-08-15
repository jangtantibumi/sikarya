<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNumberingSeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'nullable|uuid',
            'module_code' => 'required|string|max:50',
            'document_type' => 'required|string|max:50',
            'prefix' => 'required|string|max:50',
            'suffix' => 'nullable|string|max:50',
            'length' => 'integer|min:1|max:10',
            'current_number' => 'integer|min:0',
            'reset_cycle' => 'required|in:never,yearly,monthly,daily',
            'is_active' => 'boolean',
        ];
    }
}
