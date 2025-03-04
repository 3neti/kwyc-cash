<?php

namespace App\Listeners;

use App\Notifications\GeneratedCashVouchers;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\CashVouchersGenerated;

class SendGeneratedCashVouchers
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
    public function handle(CashVouchersGenerated $event): void
    {
        $user = $event->user;
        $collection = $event->collection;

        $user->notify(new GeneratedCashVouchers($collection));
    }
}
