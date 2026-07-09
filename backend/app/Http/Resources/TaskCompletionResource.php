<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCompletionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'task_id' => $this->task_id,
            'completed_at' => $this->completed_at,
            'completion_date' => $this->completion_date,
            'created_at' => $this->created_at,
        ];
    }
}
