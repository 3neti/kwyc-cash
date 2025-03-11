<?php

namespace App\Notifications;

use LBHurtado\EngageSpark\EngageSparkMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use App\Data\VoucherData;

class ContactVoucher extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected VoucherData $data){}

    public function via(object $notifiable): array
    {
        return ['engage_spark'];
    }

    public function toEngageSpark(object $notifiable): EngageSparkMessage
    {
        $message = __('This voucher code :code with the amount of :amount pesos was shared with you by :name.', [
            'code' => $this->data->code,
            'amount' => $this->data->amount,
            'name' => 'Lester'
        ]);

        return (new EngageSparkMessage())
            ->content($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'data' => $this->data
        ];
    }
}
