<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_currency_id' => 'sometimes|required|uuid|exists:finance_currencies,id',
            'to_currency_id' => 'sometimes|required|uuid|exists:finance_currencies,id',
            'rate_date' => 'sometimes|required|date',
            'rate_type' => 'sometimes|required|in:spot,monthly,corporate,tax',
            'rate' => 'sometimes|required|numeric|gt:0',
            'is_active' => 'boolean',
        ];
    }
}
