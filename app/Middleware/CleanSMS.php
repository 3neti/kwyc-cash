<?php

namespace App\Middleware;

use Closure;

class CleanSMS implements SMSMiddlewareInterface
{
    public function handle(string $message, string $from, string $to, Closure $next)
    {
        // Remove extra spaces, leading/trailing spaces, and convert to uppercase
        $message = trim(preg_replace('/\s+/', ' ', $message));

        return $next($message, $from, $to);
    }
}
