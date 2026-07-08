<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAchievementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'achievement' => new AchievementResource($this->whenLoaded('achievement')),
            'earned_at' => $this->earned_at,
            'reward_coins' => $this->reward_coins,
        ];
    }
}
