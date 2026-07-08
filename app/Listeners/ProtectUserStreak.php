<?php

namespace App\Listeners;

use App\Events\FreezeUsed;

class ProtectUserStreak
{
    public function handle(FreezeUsed $event): void
    {
        // Future notifications/activity feeds can hook into this event.
    }
}
