<?php

namespace App\Listeners;

use App\Notifications\SendLoginMagicLinkNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use MagicLink\Actions\LoginAction;
use App\Events\LoggedInViaMobile;
use MagicLink\MagicLink;
use App\Models\User;

class SendLoginMagicLink
{
    public function __construct(){}

    public function handle(LoggedInViaMobile $event): void
    {
        if (app()->runningInConsole()) return;

        $user = $event->user;
        if ($user instanceof User) {
            $action = new LoginAction($user);
            $action->response(redirect('/dashboard'));
            $urlToDashBoard = MagicLink::create($action)->url;
            $user->notify(new SendLoginMagicLinkNotification($urlToDashBoard));
        }

    }
}
