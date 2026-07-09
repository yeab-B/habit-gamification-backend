<?php

namespace App\Http\Requests\Friend;

use App\Http\Requests\ApiFormRequest;

class SendFriendRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
        ];
    }
}
