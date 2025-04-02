<?php

namespace App\Listeners;

use App\Notifications\SendSMSRegistrationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\RegisteredViaSMS;

class SendSMSRegistration
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
    public function handle(RegisteredViaSMS $event): void
    {
        $user = $event->user;
        $user->notify(new SendSMSRegistrationNotification($event->user));
    }
}
