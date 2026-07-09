<?php

namespace App\Events;

use App\Models\Promise;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PromiseCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Promise $promise)
    {
    }
}
