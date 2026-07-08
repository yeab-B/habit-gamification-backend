<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'date_range' => $this->resource['date_range'],
            'completion_statistics' => $this->resource['completion_statistics'],
            'task_statistics' => $this->resource['task_statistics'],
            'streak_statistics' => $this->resource['streak_statistics'],
            'coin_statistics' => $this->resource['coin_statistics'],
            'challenge_statistics' => $this->resource['challenge_statistics'],
        ];
    }
}
