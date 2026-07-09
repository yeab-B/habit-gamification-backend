<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\ApiFormRequest;

class CompleteTaskRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task !== null && $this->user()?->can('complete', $task) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
