<?php

namespace App\Http\Requests\Challenge;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreChallengeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
        ];
    }
}
