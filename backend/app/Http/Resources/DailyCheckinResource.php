<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyCheckinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->date,
            'tasks_completed' => $this->tasks_completed,
            'total_points' => $this->total_points,
            'is_completed' => $this->is_completed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
