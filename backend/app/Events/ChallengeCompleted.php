<?php

namespace App\Events;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Challenge $challenge,
        public readonly User $user
    ) {
    }
}
