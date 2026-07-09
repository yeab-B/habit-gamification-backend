<?php

namespace App\Http\Requests\Promise;

use App\Http\Requests\ApiFormRequest;

class StorePromiseRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
