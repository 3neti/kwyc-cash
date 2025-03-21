<?php

namespace App\Notifications;

use LBHurtado\EngageSpark\EngageSparkMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use App\Facades\Quote;

class SendInspiration extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $message;

    public function __construct()
    {
        $this->message = Quote::get();
    }

    public function via(object $notifiable): array
    {
        return ['engage_spark'];
    }

    public function toEngageSpark(object $notifiable): EngageSparkMessage
    {
        return (new EngageSparkMessage())
            ->content($this->message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'mobile' => $notifiable->mobile,
            'message' => $this->message
        ];
    }
}
