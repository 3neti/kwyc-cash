<?php

namespace App\Middleware;

use Illuminate\Support\Facades\Log;
use Closure;

class BlockBlacklistedWords implements SMSMiddlewareInterface
{
    protected array $blacklist = ['spam', 'scam', 'fraud'];

    public function handle(string $message, string $from, string $to, Closure $next)
    {
        foreach ($this->blacklist as $word) {
            if (stripos($message, $word) !== false) {
                Log::warning("Blocked SMS due to blacklisted word", [
                    'message' => $message,
                    'from' => $from,
                    'to' => $to,
                ]);

                return response()->json(['error' => 'Message contains prohibited words.'], 403);
            }
        }

        return $next($message, $from, $to);
    }
}
