<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class SendSMSRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📱 Welcome to the SMS Voucher System')
            ->greeting("Hello {$this->user->name}!")
            ->line('You have successfully registered to our SMS Voucher System.')
            ->line('Here’s how you can use it:')
            ->line('---')
            ->line('**🛠 GENERATE COMMAND**')
            ->line('```
GENERATE <modifiers> <dedication text>
```')
            ->line('**Modifiers & Symbols:**')
            ->line('| Symbol        | Field     | Description                                | Example Input     |')
            ->line('|---------------|-----------|--------------------------------------------|--------------------|')
            ->line('| `$` / `₱`     | value     | Voucher amount                             | ₱100 or $200       |')
            ->line('| `*`           | qty       | Number of vouchers                         | *3                 |')
            ->line('| `!`           | duration  | Voucher validity (e.g., 2H, PT12H)         | !2H or !PT12H      |')
            ->line('| `@`           | feedback  | Mobile to receive reply                    | @09171234567       |')
            ->line('| `#`           | tag       | Campaign or category label                 | #ReliefAid         |')
            ->line('| `>` `:` `&`   | mobile    | Lock voucher to a mobile number            | >09171234567       |')
            ->line('| *(none)*      | dedication| Message/dedication                         | Para sa barangay   |')
            ->line('---')
            ->line('**✅ Example**')
            ->line('```
GENERATE ₱200 *3 !2H >09171234567 Para sa barangay hall
```')
            ->line('---')
            ->line('**💳 REDEMPTION COMMAND**')
            ->line('```
<VOUCHER_CODE>
<VOUCHER_CODE> <MOBILE>
```')
            ->line('Examples:')
            ->line('```
AB12CD34EF
AB12CD34EF 09175551234
```')
            ->line('---')
            ->line('**🧪 SMS Summary**')
            ->line('| Action       | Format                                           | Example                                 |')
            ->line('|--------------|--------------------------------------------------|-----------------------------------------|')
            ->line('| Generate     | GENERATE ₱<amount> *<qty> !<duration> >/<mobile> | GENERATE ₱100 *2 !PT2H >0917... Message |')
            ->line('| Redeem       | <voucher_code>                                   | AB12CD34EF                              |')
            ->line('| Redeem for   | <voucher_code> <mobile>                          | AB12CD34EF 09175559999                  |')
            ->line('---')
            ->line('✨ Notes:')
            ->line('- PT2H = valid for 2 hours (ISO 8601).')
            ->line('- First 10 voucher codes are shown in SMS replies.')
            ->line('- Replies are concise and friendly.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
