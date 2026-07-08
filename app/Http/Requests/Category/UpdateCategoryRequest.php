<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\ApiFormRequest;

class UpdateCategoryRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category !== null && $this->user()?->can('update', $category) === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'icon' => ['sometimes', 'nullable', 'string'],
            'color' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
