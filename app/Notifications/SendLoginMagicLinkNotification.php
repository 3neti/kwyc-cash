<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use LBHurtado\EngageSpark\EngageSparkMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;

class SendLoginMagicLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $url){}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['engage_spark'];
    }

    public function toEngageSpark(object $notifiable): EngageSparkMessage
    {
        $message = __('You may login via :url.', [
            'url' => $this->url
        ]);

        return (new EngageSparkMessage())
            ->content($message);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'url' => $this->url
        ];
    }
}
