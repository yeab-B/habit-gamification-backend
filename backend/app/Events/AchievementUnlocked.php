<?php

namespace App\Events;

use App\Models\UserAchievement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly UserAchievement $userAchievement)
    {
    }
}
