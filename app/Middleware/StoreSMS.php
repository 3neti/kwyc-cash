<?php

namespace App\Middleware;

use Closure;
use App\Models\SMS;
use Illuminate\Support\Facades\Log;

class StoreSMS implements SMSMiddlewareInterface
{
    public function handle(string $message, string $from, string $to, Closure $next)
    {
        SMS::create([
            'from' => $from,
            'to' => $to,
            'message' => $message,
        ]);
        Log::info("🛠 Running StoreSMS Middleware", compact('message', 'from', 'to'));

        return $next($message, $from, $to);
    }
}
