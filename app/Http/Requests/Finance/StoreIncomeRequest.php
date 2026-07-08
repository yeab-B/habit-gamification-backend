<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiFormRequest;

class StoreIncomeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'income_source_id' => ['required', 'uuid', 'exists:income_sources,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'income_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
