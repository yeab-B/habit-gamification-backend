<?php

namespace App\Http\Requests\Friend;

use App\Http\Requests\ApiFormRequest;
use App\Models\Friendship;

class RejectFriendRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $friendship = $this->friendship();

        if ($friendship === null) {
            return $this->user() !== null;
        }

        return $this->user()?->can('reject', $friendship) === true;
    }

    public function rules(): array
    {
        return [
            'friendship_id' => ['required', 'uuid', 'exists:friendships,id'],
        ];
    }

    public function friendship(): ?Friendship
    {
        $id = $this->input('friendship_id');

        return is_string($id) ? Friendship::query()->find($id) : null;
    }
}
