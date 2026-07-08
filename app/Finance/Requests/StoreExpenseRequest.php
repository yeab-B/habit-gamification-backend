<?php

namespace App\Finance\Requests;

use App\Http\Requests\ApiFormRequest;

class StoreExpenseRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'uuid', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
