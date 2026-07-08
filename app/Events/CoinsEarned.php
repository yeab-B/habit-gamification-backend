<?php

namespace App\Events;

use App\Models\CoinTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoinsEarned
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CoinTransaction $transaction)
    {
    }
}
