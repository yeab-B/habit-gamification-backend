<?php

namespace App\Events;

use App\Models\Freeze;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FreezeSent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Freeze $freeze)
    {
    }
}
