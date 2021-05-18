<?php

namespace App\Models;

use App\Events\NewCallbackEvent;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $dispatchesEvents = [
        'created' => NewCallbackEvent::class,
    ];


}
