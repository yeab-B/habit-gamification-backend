<?php

namespace App\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RewardWalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'available_balance' => $this->available_balance,
            'locked_balance' => $this->locked_balance,
        ];
    }
}
