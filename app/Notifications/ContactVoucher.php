<?php

namespace App\Notifications;

use LBHurtado\EngageSpark\EngageSparkMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\{Carbon, Number};
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
        $message = __('This voucher code :code with the amount of :amount was shared with you by :name. It will expire on :date.', [
            'code' => $this->data->code,
            'amount' => Number::currency($this->data->amount),
            'name' => mask_name($this->data->owner->name),
            'date' => Carbon::parse($this->data->expires_at)->translatedFormat('F j, Y \\a\\t g:i A'),
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
