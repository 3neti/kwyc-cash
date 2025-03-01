<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;

class VoucherRedeemed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Voucher $voucher){}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
