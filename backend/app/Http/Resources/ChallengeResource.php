<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration_days' => $this->duration_days,
            'status' => $this->status,
            'owner' => $this->relationLoaded('user') ? new UserResource($this->user) : null,
            'categories_count' => $this->whenCounted('categories'),
            'participants_count' => $this->whenCounted('participants'),
            'categories' => $this->relationLoaded('categories') ? CategoryResource::collection($this->categories) : [],
            'participants' => $this->relationLoaded('participants') ? UserResource::collection($this->participants) : [],
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
