<?php

namespace App\Handlers\AutoReplies;

use App\Contracts\AutoReplyInterface;

class HelpAutoReply implements AutoReplyInterface
{
    public function reply(string $from, string $to, string $message): string
    {
        return "For support, reply with 'SUPPORT'. To contact an agent, reply 'AGENT'.";
    }
}
