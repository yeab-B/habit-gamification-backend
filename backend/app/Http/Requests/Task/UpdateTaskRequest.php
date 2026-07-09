<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task !== null && $this->user()?->can('update', $task) === true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'points' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
