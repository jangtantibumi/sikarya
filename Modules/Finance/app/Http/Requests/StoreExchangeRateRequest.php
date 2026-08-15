<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_currency_id' => 'required|uuid|exists:finance_currencies,id',
            'to_currency_id' => 'required|uuid|exists:finance_currencies,id',
            'rate_date' => 'required|date',
            'rate_type' => 'required|in:spot,monthly,corporate,tax',
            'rate' => 'required|numeric|gt:0',
            'is_active' => 'boolean',
        ];
    }
}
