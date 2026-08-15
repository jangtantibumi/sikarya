<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:150',
            'net_days' => 'sometimes|required|integer|min:0',
            'discount_days' => 'integer|min:0',
            'discount_percentage' => 'numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
