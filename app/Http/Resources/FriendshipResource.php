<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender' => $this->relationLoaded('sender') ? new UserResource($this->sender) : null,
            'receiver' => $this->relationLoaded('receiver') ? new UserResource($this->receiver) : null,
            'status' => $this->status,
            'responded_at' => $this->responded_at,
            'created_at' => $this->created_at,
        ];
    }
}
