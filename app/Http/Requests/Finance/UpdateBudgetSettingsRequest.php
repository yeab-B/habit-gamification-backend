<?php

namespace App\Finance\Requests;

use App\Http\Requests\ApiFormRequest;

class UpdateBudgetSettingsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'asrat_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'needs_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'emergency_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'investment_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'reward_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
