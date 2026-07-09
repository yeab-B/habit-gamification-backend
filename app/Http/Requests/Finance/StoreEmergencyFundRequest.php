<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiFormRequest;

class StoreEmergencyFundRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'goal_amount' => ['required', 'numeric', 'min:1'],
            'current_amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
