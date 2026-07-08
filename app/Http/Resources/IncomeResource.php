<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'income_source' => $this->relationLoaded('incomeSource') ? new IncomeSourceResource($this->incomeSource) : null,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'income_date' => $this->income_date?->toDateString(),
            'description' => $this->description,
            'asrat' => $this->when(isset($this->asrat), $this->asrat),
            'remaining_after_asrat' => $this->when(isset($this->remaining_after_asrat), $this->remaining_after_asrat),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
