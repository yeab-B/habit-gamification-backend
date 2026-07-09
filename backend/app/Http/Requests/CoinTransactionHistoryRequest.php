<?php

namespace App\Http\Requests;

class CoinTransactionHistoryRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:earn,spend,bonus,penalty'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
