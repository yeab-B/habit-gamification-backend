<?php

namespace App\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->relationLoaded('category') ? $this->category?->name : null,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date?->toDateString(),
            'description' => $this->description,
        ];
    }
}
