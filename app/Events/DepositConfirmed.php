<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Models\User;

/**
 * Event broadcast when a deposit is confirmed.
 */
class DepositConfirmed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User   $user       The user whose deposit was confirmed.
     * @param float  $amount     The amount deposited.
     * @param Carbon $updated_at The timestamp of the deposit confirmation.
     */
    public function __construct(
        private User $user,
        private float $amount,
        private Carbon $updated_at
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    /**
     * Get the name of the event to broadcast as.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'deposit.confirmed';
    }

    /**
     * The data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $formattedAmount = (new \NumberFormatter('en-PH', \NumberFormatter::CURRENCY))->formatCurrency($this->amount, 'PHP');
        $timeAgo = $this->updated_at->diffForHumans();

        return [
            'amount'      => $this->amount,
            'mobile'      => $this->user->mobile,
            'confirmedAt' => $this->updated_at->format('Y-m-d H:i:s'),
            'message'     => "{$formattedAmount} deposit confirmed {$timeAgo}",
        ];
    }
}
