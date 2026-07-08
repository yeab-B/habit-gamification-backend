<?php

namespace App\Finance\Requests;

use App\Http\Requests\ApiFormRequest;

class InvestmentTransactionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:deposit,withdraw,profit,loss'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['sometimes', 'date'],
        ];
    }
}
