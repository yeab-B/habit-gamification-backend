<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => $this->resource['user'],
            'current_challenge' => $this->resource['current_challenge'],
            'today' => $this->resource['today'],
            'coins' => $this->resource['coins'],
            'streak' => $this->resource['streak'],
            'weekly_progress' => $this->resource['weekly_progress'],
            'monthly_progress' => $this->resource['monthly_progress'],
            'achievement_summary' => [
                'total_achievements' => $this->resource['achievement_summary']['total_achievements'],
                'recently_unlocked' => UserAchievementResource::collection(
                    collect($this->resource['achievement_summary']['recently_unlocked'])
                ),
                'total_reward_coins' => $this->resource['achievement_summary']['total_reward_coins'],
            ],
        ];
    }
}
