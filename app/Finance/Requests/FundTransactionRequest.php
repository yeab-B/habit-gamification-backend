<?php

namespace App\Finance\Requests;

use App\Http\Requests\ApiFormRequest;

class FundTransactionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
