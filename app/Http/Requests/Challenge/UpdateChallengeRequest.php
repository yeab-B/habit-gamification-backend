<?php

namespace App\Http\Requests\Challenge;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateChallengeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $challenge = $this->route('challenge');

        return $challenge !== null && $this->user()?->can('update', $challenge) === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'duration_days' => ['sometimes', 'required', 'integer', 'min:1'],
            'category_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'category_ids.*' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
        ];
    }
}
