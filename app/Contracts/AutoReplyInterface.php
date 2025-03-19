<?php

namespace App\Contracts;

interface AutoReplyInterface
{
    public function reply(string $from, string $to, string $message): string;
}
