<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiFormRequest;

class UpdateIncomeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $income = $this->route('income');

        return $income !== null && $this->user()?->can('update', $income) === true;
    }

    public function rules(): array
    {
        return [
            'income_source_id' => ['sometimes', 'required', 'uuid', 'exists:income_sources,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'income_date' => ['sometimes', 'required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
