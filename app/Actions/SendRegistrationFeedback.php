<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Services\OmniChannelService;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class SendRegistrationFeedback
{
    use AsAction;

    /**
     * Send a registration confirmation SMS to the newly registered user.
     *
     * @param  User  $user  The user who has just been registered.
     * @return void
     */
    public function handle(User $user): void
    {
        $message = sprintf(
            "Hello %s! You've been registered in our system. Please keep this number safe for future logins.",
            $user->name
        );

        Log::info('📤 Sending registration feedback SMS', [
            'to' => $user->mobile,
            'message' => $message,
        ]);

        // Dispatch message to the registered mobile number
        $sms = app(OmniChannelService::class);
        $sms->send($user->mobile, $message);
    }
}
