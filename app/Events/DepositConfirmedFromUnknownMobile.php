<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;


/**
 * Event broadcast when a deposit is confirmed.
 */
class DepositConfirmedFromUnknownMobile implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param string $mobile     The mobile number whose deposit was confirmed.
     * @param float  $amount     The amount deposited.
     */
    public function __construct(
        private readonly string $mobile,
        private readonly float  $amount,
        private readonly string $name
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('mobile'),
        ];
    }

    /**
     * Get the name of the event to broadcast as.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'deposit.confirmed-from-unknown-mobile';
    }

    /**
     * The data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'mobile'      => $this->mobile,
            'amount'      => $this->amount,
            'name'        => $this->name,
        ];
    }
}
