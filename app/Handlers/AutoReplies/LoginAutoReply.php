<?php

namespace App\Handlers\AutoReplies;

use App\Contracts\AutoReplyInterface;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;
use App\Models\User;

class LoginAutoReply implements AutoReplyInterface
{
    public function reply(string $from, string $to, string $message): ?string
    {
        if ($user = User::where('mobile', $from)->first()) {
            $action = new LoginAction($user);
            $action->response(redirect('/dashboard'));
            $urlToDashBoard = MagicLink::create($action)->url;

            return __('Login: :url', ['url' => $urlToDashBoard]);
        }

        return null; // Now valid with the updated interface
    }
}
