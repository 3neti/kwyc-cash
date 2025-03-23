<?php

namespace App\Pipes;

class TrimTo160Characters
{
    public function handle(string $message, \Closure $next)
    {
        // Enforce hard 160-char limit
        $message = mb_substr($message, 0, 160);

        return $next($message);
    }
}
