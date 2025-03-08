<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use LBHurtado\EngageSpark\EngageSparkMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use App\Data\VoucherData;

class DisbursementFeedback extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected VoucherData $data){}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'engage_spark'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The voucher code')
            ->line($this->data->code)
            ->line('was disbursed with the amount of')
            ->line($this->data->cash->value . ' pesos.')
            ->line('to mobile # ' . $this->data->mobile . '.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toEngageSpark(object $notifiable): EngageSparkMessage
    {
        $message = __('The voucher code :code was disbursed with the amount of :amount pesos to mobile # :mobile.', [
            'code' => $this->data->code,
            'amount' => $this->data->amount,
            'mobile' => $this->data->mobile
        ]);

        return (new EngageSparkMessage())
            ->content($message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
