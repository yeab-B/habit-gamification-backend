<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoinTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'source' => $this->source,
            'amount' => $this->amount,
            'balance_after' => $this->balance_after,
            'description' => $this->description,
            'reference_id' => $this->reference_id,
            'created_at' => $this->created_at,
        ];
    }
}
