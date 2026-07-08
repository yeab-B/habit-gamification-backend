<?php

namespace App\Events;

use App\Models\Friendship;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRemoved
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Friendship $friendship)
    {
    }
}
