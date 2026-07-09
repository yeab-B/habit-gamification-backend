<?php

namespace App\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyFundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progress = (float) $this->goal_amount > 0
            ? round(((float) $this->current_amount / (float) $this->goal_amount) * 100, 2)
            : 0;

        return [
            'id' => $this->id,
            'goal_amount' => $this->goal_amount,
            'current_amount' => $this->current_amount,
            'status' => $this->status,
            'progress_percentage' => $progress,
        ];
    }
}
