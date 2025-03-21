<?php

namespace App\Handlers\AutoReplies;

use App\Notifications\SendLoginMagicLinkNotification;
use App\Contracts\AutoReplyInterface;
use App\Models\User;

class LoginAutoReply implements AutoReplyInterface
{
    public function reply(string $from, string $to, string $message): ?string
    {
        if ($user = User::where('mobile', $from)->first()) {
            $user->notify(new SendLoginMagicLinkNotification('http://google.com'));

            return __(':name, your login link was sent to your registered email address.', ['name' => $user->name]);
        }

        return null; // Now valid with the updated interface
    }
}
