<?php

namespace App\Pipes;

class CapitalizeAndPunctuate
{
    public function handle(string $message, \Closure $next)
    {
        $message = ucfirst(trim($message));

        if (!preg_match('/[.!?]$/', $message)) {
            $message .= '.';
        }

        return $next($message);
    }
}
