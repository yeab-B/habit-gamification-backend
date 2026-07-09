<?php

namespace App\Events\Finance;

use App\Models\Finance\RewardWallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RewardEarned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly RewardWallet $wallet,
        public readonly float $amount
    ) {
    }
}
