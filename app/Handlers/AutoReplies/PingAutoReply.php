<?php

namespace App\Handlers\AutoReplies;

use App\Contracts\AutoReplyInterface;

class PingAutoReply implements AutoReplyInterface
{
    public function reply(string $from, string $to, string $message): string
    {
        return "PONG! Machine is running. Timestamp: " . now();
    }
}
