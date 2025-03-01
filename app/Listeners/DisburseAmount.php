<?php

namespace App\Listeners;

use App\Events\VoucherRedeemed;
use App\Models\Cash;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DisburseAmount
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
    public function handle(VoucherRedeemed $event): void
    {
        $voucher = $event->voucher;
        if ($voucher instanceof Voucher) {
            $mobile = $voucher->redeemer->redeemer->mobile;
            $money = $voucher->getEntities(Cash::class)->first()->value;
            $amount = $money->getAmount()->toFloat();
        }
    }
}
