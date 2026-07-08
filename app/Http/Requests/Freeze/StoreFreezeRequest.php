<?php

namespace App\Http\Requests\Freeze;

use App\Http\Requests\ApiFormRequest;

class StoreFreezeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
