<?php

namespace App\Listeners;

use App\Events\FriendRemoved;
use App\Events\FriendRequestAccepted;
use App\Events\FriendRequestSent;

class RecordFriendActivity
{
    public function handle(FriendRequestSent|FriendRequestAccepted|FriendRemoved $event): void
    {
        // Future hook for notifications, activity feeds, and achievements.
    }
}
