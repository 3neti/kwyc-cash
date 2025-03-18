<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\SMSArrived;
use App\Models\SMS;

class PersistSMS implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SMSArrived $event): void
    {
        $sms = SMS::createFromSMSData($event->SMSData);
    }
}
