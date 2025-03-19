<?php

namespace App\Handlers\AutoReplies;

use App\Contracts\AutoReplyInterface;
use Illuminate\Support\Number;
use App\Models\User;

class BalanceAutoReply implements AutoReplyInterface
{
    public function reply(string $from, string $to, string $message): ?string
    {
        if ($user = User::where('mobile', $from)->first()) {
            return __('Balance: :balance', ['balance' => Number::currency($user->balanceFloat)]);
        }

        return null;
    }
}
