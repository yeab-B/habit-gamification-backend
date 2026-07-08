<?php

namespace App\Finance\Requests;

use App\Http\Requests\ApiFormRequest;

class StoreInvestmentRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:Business,Stocks,Crypto,Education,Courses,Books,Savings'],
            'description' => ['nullable', 'string'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
