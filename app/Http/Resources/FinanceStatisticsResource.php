<?php

namespace App\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceStatisticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
