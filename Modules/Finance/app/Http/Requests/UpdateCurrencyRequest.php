<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|string|max:10',
            'name' => 'sometimes|required|string|max:100',
            'symbol' => 'sometimes|required|string|max:10',
            'decimal_places' => 'integer|min:0|max:6',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
