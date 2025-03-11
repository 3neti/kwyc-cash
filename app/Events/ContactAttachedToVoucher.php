<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Cash, Contact, User};
use Illuminate\Broadcasting\Channel;

class ContactAttachedToVoucher
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user, public Voucher $voucher){}

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
        return 'contact.attached';
    }

    /**
     * The data to broadcast with the event.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $cash = $this->voucher->getEntities(Cash::class)->first();
        $amount = $cash->value->getAmount()->toFloat();
        $formattedAmount = (new \NumberFormatter('en-PH', \NumberFormatter::CURRENCY))->formatCurrency($amount, 'PHP');
        $contact = $this->voucher->getEntities(Contact::class)->first();

        return [
            'amount'      => $amount,
            'mobile'      => $contact->mobile,
            'message'     => "{$formattedAmount} attached to {$contact->mobile}",
        ];
    }
}
