<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromiseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'promise_date' => $this->promise_date?->toDateString(),
            'validation_date' => $this->validation_date?->toDateString(),
            'status' => $this->status,
            'reason' => $this->reason,
            'reward_coins' => $this->reward_coins,
            'penalty_coins' => $this->penalty_coins,
            'validated_at' => $this->validated_at,
            'created_at' => $this->created_at,
        ];
    }
}
