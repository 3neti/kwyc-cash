<?php

namespace App\Handlers\AutoReplies;

use App\Contracts\AutoReplyInterface;

class HelloAutoReply implements AutoReplyInterface
{
    public function reply(string $from, string $to, string $message): string
    {
        return "Hello! How can we assist you today?";
    }
}
