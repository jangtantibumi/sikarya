<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxMasterRequest extends FormRequest
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
            'rate' => 'required|numeric|min:0|max:100',
            'tax_type' => 'required|in:vat,withholding,sales_tax,service_tax',
            'calculation_type' => 'required|in:exclusive,inclusive',
            'chart_of_account_id' => 'nullable|uuid|exists:finance_chart_of_accounts,id',
            'is_active' => 'boolean',
        ];
    }
}
