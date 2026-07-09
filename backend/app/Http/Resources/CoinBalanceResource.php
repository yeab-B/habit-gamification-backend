<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoinBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'balance' => $this->resource['balance'],
        ];
    }
}
