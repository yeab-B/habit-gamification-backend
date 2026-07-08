<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $friend */
        $friend = $this->resource['friend'];

        return [
            'id' => $friend->id,
            'name' => $friend->name,
            'avatar' => $friend->avatar,
            'current_streak' => $friend->streak?->current_streak ?? 0,
            'coin_balance' => $friend->getAttribute('coin_balance') ?? 0,
            'friendship_date' => $this->resource['friendship']->responded_at,
            'friendship_id' => $this->resource['friendship']->id,
        ];
    }
}
